(function(window,$){
    'use strict';
    var $module=$('#reference-master-module');
    var $table=$('#module-table');
    if(!$||!$module.length||!$table.length)return;
    var columns=$table.find('thead th').map(function(){
        var key=$(this).data('column');
        return {data:key,orderable:['name','code'].includes(key),searchable:['name','code'].includes(key)};
    }).get();
    function filters(){return{status:$('#filter-status').val(),from_date:$('#filter-from').val(),to_date:$('#filter-to').val()};}
    var table=initializeDataTable({selector:'#module-table',url:$module.data('index-url'),filters:filters,columns:columns,order:[]});
    var timer;$('#filter-search').on('input',function(){var value=$(this).val();clearTimeout(timer);timer=setTimeout(function(){table.search(value).draw();},300);});
    $('#filter-status,#filter-from,#filter-to').on('change',function(){table.ajax.reload(null,false);});
    $('#reset-filters').on('click',function(){$('#module-filters').trigger('reset');table.search('').ajax.reload(null,false);});
    $(document).on('click','.delete-record',function(){var url=$(this).data('url');Swal.fire({title:'Delete this record?',icon:'warning',showCancelButton:true,confirmButtonText:'Delete'}).then(function(result){if(!result.isConfirmed)return;CholavinAjax.request({url:url,method:'DELETE',onSuccess:function(response){toastr.success(response.message);table.ajax.reload(null,false);}});});});
    $('#download-pdf').on('click',function(){var params=filters();params.search=$('#filter-search').val();window.location.href=$module.data('pdf-url')+'?'+new URLSearchParams(params).toString();});
})(window,window.jQuery);
