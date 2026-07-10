(function () {
  var STORAGE_KEY = "preferredLanguage";
  var DEFAULT_LANGUAGE = "en";
  var SUPPORTED_TRANSLATION = "ta";
  var TRANSLATE_SCRIPT_ID = "google-translate-script";
  var SELECTOR_WAIT_LIMIT = 40;

  function getSavedLanguage() {
    try {
      var saved = localStorage.getItem(STORAGE_KEY);
      return saved === SUPPORTED_TRANSLATION ? SUPPORTED_TRANSLATION : DEFAULT_LANGUAGE;
    } catch (error) {
      return DEFAULT_LANGUAGE;
    }
  }

  function saveLanguage(language) {
    try {
      localStorage.setItem(STORAGE_KEY, language);
    } catch (error) {
      // Ignore storage issues and continue.
    }
  }

  function setCookie(name, value) {
    var cookieValue = name + "=" + value + "; path=/";
    document.cookie = cookieValue;

    if (!window.location.hostname) {
      return;
    }

    document.cookie = cookieValue + "; domain=" + window.location.hostname;

    var hostParts = window.location.hostname.split(".");
    if (hostParts.length > 1) {
      document.cookie =
        cookieValue + "; domain=." + hostParts.slice(-2).join(".");
    }
  }

  function clearTranslateCookie() {
    var expired = "Thu, 01 Jan 1970 00:00:00 GMT";
    document.cookie = "googtrans=; expires=" + expired + "; path=/";

    if (!window.location.hostname) {
      return;
    }

    document.cookie =
      "googtrans=; expires=" +
      expired +
      "; path=/; domain=" +
      window.location.hostname;

    var hostParts = window.location.hostname.split(".");
    if (hostParts.length > 1) {
      document.cookie =
        "googtrans=; expires=" +
        expired +
        "; path=/; domain=." +
        hostParts.slice(-2).join(".");
    }
  }

  function ensureTranslateContainer() {
    var container = document.getElementById("google_translate_element");

    if (!container) {
      container = document.createElement("div");
      container.id = "google_translate_element";
      container.className = "google-translate-element";
      document.body.appendChild(container);
    }

    return container;
  }

  function syncToggleState(language) {
    document.querySelectorAll(".header-translate").forEach(function (wrapper) {
      var label = wrapper.querySelector(".translate-toggle-text");

      if (label) {
        label.textContent = language.toUpperCase();
      }

      wrapper.querySelectorAll(".translate-option").forEach(function (option) {
        option.classList.toggle(
          "is-active",
          option.getAttribute("data-lang") === language,
        );
      });
    });
  }

  function closeAllDropdowns() {
    document.querySelectorAll(".header-translate").forEach(function (wrapper) {
      wrapper.classList.remove("is-open");

      var toggle = wrapper.querySelector(".translate-toggle");
      if (toggle) {
        toggle.setAttribute("aria-expanded", "false");
      }
    });
  }

  function triggerChange(element) {
    element.dispatchEvent(new Event("change", { bubbles: true }));
  }

  function waitForTranslateSelect(callback) {
    var attempts = 0;
    var interval = window.setInterval(function () {
      var select = document.querySelector(".goog-te-combo");

      if (select) {
        window.clearInterval(interval);
        callback(select);
        return;
      }

      attempts += 1;

      if (attempts >= SELECTOR_WAIT_LIMIT) {
        window.clearInterval(interval);
      }
    }, 250);
  }

  function applyTamilTranslation() {
    setCookie("googtrans", "/auto/" + SUPPORTED_TRANSLATION);

    waitForTranslateSelect(function (select) {
      if (select.value !== SUPPORTED_TRANSLATION) {
        select.value = SUPPORTED_TRANSLATION;
        triggerChange(select);
      }
    });
  }

  function applyLanguage(language) {
    var currentLanguage = getSavedLanguage();

    syncToggleState(language);
    closeAllDropdowns();
    saveLanguage(language);

    if (language === DEFAULT_LANGUAGE) {
      clearTranslateCookie();
      if (
        currentLanguage !== DEFAULT_LANGUAGE ||
        document.body.classList.contains("translated-ltr") ||
        document.body.classList.contains("translated-rtl")
      ) {
        window.location.reload();
      }
      return;
    }

    applyTamilTranslation();
  }

  function bindUi() {
    document.addEventListener("click", function (event) {
      var option = event.target.closest(".translate-option");
      if (option) {
        applyLanguage(option.getAttribute("data-lang"));
        return;
      }

      var toggle = event.target.closest(".translate-toggle");
      if (toggle) {
        var wrapper = toggle.closest(".header-translate");
        var isOpen = wrapper.classList.contains("is-open");

        closeAllDropdowns();

        if (!isOpen) {
          wrapper.classList.add("is-open");
          toggle.setAttribute("aria-expanded", "true");
        }

        return;
      }

      if (!event.target.closest(".header-translate")) {
        closeAllDropdowns();
      }
    });
  }

  function loadGoogleTranslate() {
    if (document.getElementById(TRANSLATE_SCRIPT_ID)) {
      return;
    }

    ensureTranslateContainer();

    var script = document.createElement("script");
    script.id = TRANSLATE_SCRIPT_ID;
    script.src =
      "https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit";
    script.async = true;
    document.body.appendChild(script);
  }

  window.googleTranslateElementInit = function () {
    ensureTranslateContainer();

    if (!window.google || !window.google.translate) {
      return;
    }

    new window.google.translate.TranslateElement(
      {
        pageLanguage: "en",
        includedLanguages: "en,ta",
        autoDisplay: false,
      },
      "google_translate_element",
    );

    if (getSavedLanguage() === SUPPORTED_TRANSLATION) {
      applyTamilTranslation();
    } else {
      clearTranslateCookie();
    }
  };

  function init() {
    syncToggleState(getSavedLanguage());
    bindUi();
    loadGoogleTranslate();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
