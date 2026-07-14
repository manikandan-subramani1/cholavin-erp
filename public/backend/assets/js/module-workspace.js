(function (window, document) {
    'use strict';

    var $ = window.jQuery;
    if (!$ || !$.fn.DataTable) return;

    var excludedTables = ['parties-table', 'party-transactions-table'];

    function text(value) {
        if (value === null || value === undefined || value === '') return '—';
        if (typeof value === 'object') {
            if (value.name !== undefined) return text(value.name);
            if (value.title !== undefined) return text(value.title);
            return '—';
        }

        var element = document.createElement('div');
        element.innerHTML = String(value);
        return (element.textContent || element.innerText || '').trim() || '—';
    }

    function valueAtPath(record, path) {
        if (!record || typeof path !== 'string') return null;
        return path.split('.').reduce(function (value, segment) {
            return value !== null && value !== undefined ? value[segment] : null;
        }, record);
    }

    function columnKey(column) {
        return typeof column.mData === 'string' ? column.mData : '';
    }

    function usableColumns(settings) {
        return settings.aoColumns.filter(function (column) {
            var key = columnKey(column);
            return key
                && key !== 'DT_RowIndex'
                && key !== 'action'
                && key !== 'image_preview';
        });
    }

    function firstMatching(columns, patterns, ignored) {
        return columns.find(function (column) {
            var key = columnKey(column).toLowerCase();
            return !ignored.includes(key) && patterns.some(function (pattern) {
                return key === pattern || key.endsWith('.' + pattern) || key.includes(pattern);
            });
        });
    }

    function fieldMap(settings) {
        var columns = usableColumns(settings);
        var ignored = [];
        var primary = firstMatching(columns, ['product_name', 'user_name', 'document_number', 'name', 'title', 'number', 'reference', 'file'], ignored) || columns[0];
        if (primary) ignored.push(columnKey(primary).toLowerCase());

        var secondary = firstMatching(columns, ['code', 'party.name', 'party', 'type', 'date', 'mobile', 'email', 'description'], ignored);
        if (secondary) ignored.push(columnKey(secondary).toLowerCase());

        var metric = firstMatching(columns, ['balance_amount', 'current_balance', 'total_amount', 'amount', 'quantity', 'status', 'is_active', 'items_count'], ignored);

        return {columns: columns, primary: primary, secondary: secondary, metric: metric};
    }

    function recordKey(record, index) {
        return record && (record.id || record.uuid || record.number || record.code) || String(index);
    }

    function createEmptyState() {
        var state = document.createElement('div');
        state.className = 'erp-module-empty-state';
        state.innerHTML = '<div class="erp-module-empty-icon"><i class="ri-cursor-line"></i></div><h5>Select a record</h5><p>Choose a record from the list to view its details and available quick actions.</p>';
        return state;
    }

    function ensureQuickViewModal() {
        var modal = document.getElementById('erp-record-quick-view-modal');
        if (modal) return modal;

        modal = document.createElement('div');
        modal.id = 'erp-record-quick-view-modal';
        modal.className = 'modal fade';
        modal.tabIndex = -1;
        modal.innerHTML = '<div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><div><span class="erp-eyebrow">Selected record</span><h5 class="modal-title mb-0">Record details</h5></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div></div></div>';
        document.body.appendChild(modal);
        return modal;
    }

    function detailFields(record, map) {
        var grid = document.createElement('div');
        grid.className = 'erp-module-detail-grid';

        map.columns.forEach(function (column) {
            var key = columnKey(column);
            var item = document.createElement('div');
            item.className = 'erp-module-detail-item';
            var label = document.createElement('span');
            label.textContent = text(column.sTitle || key.replace(/[._-]/g, ' '));
            var value = document.createElement('strong');
            value.textContent = text(valueAtPath(record, key));
            item.appendChild(label);
            item.appendChild(value);
            grid.appendChild(item);
        });

        return grid;
    }

    function actionMarkup(record) {
        return record && typeof record.action === 'string' ? record.action : '';
    }

    function renderDetail(workspace, record, map) {
        var detail = workspace.querySelector('.erp-module-detail-panel');
        var primary = text(valueAtPath(record, columnKey(map.primary)));
        var secondary = map.secondary ? text(valueAtPath(record, columnKey(map.secondary))) : 'Selected record';

        detail.innerHTML = '';
        var header = document.createElement('div');
        header.className = 'erp-module-detail-header';
        header.innerHTML = '<div><span class="erp-eyebrow">Selected record</span><h4></h4><p></p></div><div class="erp-module-quick-actions"></div>';
        header.querySelector('h4').textContent = primary;
        header.querySelector('p').textContent = secondary;

        var actions = header.querySelector('.erp-module-quick-actions');
        var quickView = document.createElement('button');
        quickView.type = 'button';
        quickView.className = 'btn btn-secondary btn-sm erp-record-quick-view';
        quickView.innerHTML = '<i class="ri-eye-line me-1"></i>Quick View';
        actions.appendChild(quickView);

        var moduleActions = actionMarkup(record);
        if (moduleActions) actions.insertAdjacentHTML('beforeend', moduleActions);

        var content = document.createElement('div');
        content.className = 'erp-module-detail-content';
        content.appendChild(detailFields(record, map));

        detail.appendChild(header);
        detail.appendChild(content);

        quickView.addEventListener('click', function () {
            var modal = ensureQuickViewModal();
            modal.querySelector('.modal-title').textContent = primary;
            var modalBody = modal.querySelector('.modal-body');
            modalBody.innerHTML = '';
            modalBody.appendChild(detailFields(record, map));
            window.bootstrap.Modal.getOrCreateInstance(modal).show();
        });
    }

    function selectRecord(workspace, api, map, rowIndex, record) {
        workspace.dataset.selectedKey = recordKey(record, rowIndex);
        workspace.querySelectorAll('.erp-module-record').forEach(function (button) {
            button.classList.toggle('is-selected', button.dataset.rowIndex === String(rowIndex));
        });
        renderDetail(workspace, record, map);
    }

    function renderList(workspace, api, map) {
        var list = workspace.querySelector('.erp-module-record-list');
        var indexes = api.rows({page: 'current'}).indexes().toArray();
        var selectedKey = workspace.dataset.selectedKey;
        var selected = null;
        list.innerHTML = '';

        if (!indexes.length) {
            list.innerHTML = '<div class="erp-module-list-empty"><i class="ri-inbox-2-line"></i><span>No records found</span></div>';
            var detail = workspace.querySelector('.erp-module-detail-panel');
            detail.innerHTML = '';
            detail.appendChild(createEmptyState());
            return;
        }

        indexes.forEach(function (index) {
            var record = api.row(index).data();
            var key = recordKey(record, index);
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'erp-module-record';
            button.dataset.rowIndex = String(index);

            var copy = document.createElement('span');
            copy.className = 'erp-module-record-copy';
            var title = document.createElement('strong');
            title.textContent = text(valueAtPath(record, columnKey(map.primary)));
            var meta = document.createElement('small');
            meta.textContent = map.secondary ? text(valueAtPath(record, columnKey(map.secondary))) : 'View details';
            copy.appendChild(title);
            copy.appendChild(meta);
            button.appendChild(copy);

            if (map.metric) {
                var metric = document.createElement('span');
                metric.className = 'erp-module-record-metric';
                metric.textContent = text(valueAtPath(record, columnKey(map.metric)));
                button.appendChild(metric);
            }

            button.addEventListener('click', function () {
                selectRecord(workspace, api, map, index, record);
            });
            list.appendChild(button);

            if (selectedKey && String(key) === String(selectedKey)) selected = {index: index, record: record};
        });

        selected = selected || {index: indexes[0], record: api.row(indexes[0]).data()};
        selectRecord(workspace, api, map, selected.index, selected.record);
    }

    function buildWorkspace(api) {
        var table = api.table().node();
        if (!table || excludedTables.includes(table.id) || table.closest('.party-workspace') || table.closest('.modal')) return;

        var card = table.closest('.card');
        if (!card || card.closest('.erp-module-workspace')) return;

        var wrapper = table.closest('.dataTables_wrapper');
        if (!wrapper) return;

        var workspace = document.createElement('div');
        workspace.className = 'erp-module-workspace';
        card.parentNode.insertBefore(workspace, card);
        workspace.appendChild(card);
        card.classList.add('erp-module-list-panel');
        table.classList.add('erp-module-source-table');

        var list = document.createElement('div');
        list.className = 'erp-module-record-list';
        table.parentNode.insertBefore(list, table);

        var detail = document.createElement('section');
        detail.className = 'erp-module-detail-panel';
        detail.appendChild(createEmptyState());
        workspace.appendChild(detail);

        var map = fieldMap(api.settings()[0]);
        api.on('draw.erp-module-workspace', function () {
            renderList(workspace, api, map);
        });
        renderList(workspace, api, map);
    }

    $(document).on('init.dt.erp-module-workspace', function (event, settings) {
        window.setTimeout(function () {
            buildWorkspace(new $.fn.dataTable.Api(settings));
        }, 0);
    });

    $(function () {
        $.fn.dataTable.tables().forEach(function (table) {
            buildWorkspace($(table).DataTable());
        });
    });
})(window, document);
