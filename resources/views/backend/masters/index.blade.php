@extends('backend.layouts.app')
@section('title', $module['title'].' | Cholavin ERP')
@section('content')
<div class="page-title-box d-flex flex-wrap justify-content-between align-items-center gap-3"><div><span class="erp-eyebrow">{{ $module['group'] }}</span><h4 class="mb-0">{{ $module['title'] }}</h4></div><div class="d-flex gap-2">@can($moduleKey.'.export')<button id="download-master-pdf" class="btn btn-secondary"><i class="ri-file-pdf-line"></i> PDF</button>@endcan @can($moduleKey.'.create')<button id="add-master" class="btn btn-primary"><i class="ri-add-line"></i> Add New</button>@endcan</div></div>
<div class="card erp-panel mb-3"><div class="card-body"><form id="master-filters" class="row g-3"><div class="col-md-3"><label class="form-label">Status</label><select id="master-status-filter" class="form-select"><option value="">All</option><option value="1">Active</option><option value="0">Inactive</option></select></div>@if($parents->isNotEmpty())<div class="col-md-4"><label class="form-label">Parent</label><select id="master-parent-filter" class="form-select"><option value="">All</option>@foreach($parents as $parent)<option value="{{ $parent->id }}">{{ $parent->name }}</option>@endforeach</select></div>@endif<div class="col-md-2 align-self-end"><button id="reset-master-filters" type="button" class="btn btn-secondary w-100">Reset</button></div></form></div></div>
<div class="card erp-panel"><div class="card-body table-responsive"><table id="masters-table" class="table table-hover align-middle w-100"><thead><tr><th>S.No</th><th>Code</th><th>Name</th><th>Parent</th><th>Percentage</th><th>Description</th><th>Status</th><th>Created</th><th>Action</th></tr></thead></table></div></div>

<div id="master-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form id="master-form" method="POST">
            @csrf
            <div class="modal-header"><h5 id="master-modal-title" class="modal-title">Add {{ str($module['title'])->singular() }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">@include('backend.masters.'.$moduleKey.'.form')</div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Save</button></div>
        </form>
    </div></div>
</div>
@endsection
@push('scripts')
<script>
$(function(){
$.ajaxSetup({headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}});const baseUrl=@json(route('admin.masters.index',$moduleKey));const modal=new bootstrap.Modal('#master-modal');
const table=initializeDataTable({selector:'#masters-table',url:baseUrl,filters:()=>({status:$('#master-status-filter').val(),parent_id:$('#master-parent-filter').val()}),columns:[{data:'DT_RowIndex',orderable:false,searchable:false},{data:'code',name:'code'},{data:'name',name:'name'},{data:'parent.name',name:'parent.name',defaultContent:'—'},{data:'percentage',name:'percentage'},{data:'description',name:'description',defaultContent:'—'},{data:'is_active',name:'is_active'},{data:'created_at',name:'created_at'},{data:'action',orderable:false,searchable:false}],order:[[2,'asc']]});
$('#master-status-filter,#master-parent-filter').on('change',()=>table.ajax.reload());$('#reset-master-filters').on('click',()=>{document.getElementById('master-filters').reset();table.ajax.reload();});
function resetForm(){const form=document.getElementById('master-form');form.reset();form.action=baseUrl;form.querySelector('[name="_method"]').value='POST';$('#master-active').prop('checked',true);$('.is-invalid').removeClass('is-invalid');}
$('#add-master').on('click',()=>{resetForm();$('#master-modal-title').text('Add {{ str($module['title'])->singular() }}');modal.show();});
$(document).on('click','.edit-master',function(){resetForm();const b=this;const f=document.getElementById('master-form');f.action=baseUrl+'/'+b.dataset.id;f.querySelector('[name="_method"]').value='PUT';$('[name="name"]').val(b.dataset.name);$('[name="code"]').val(b.dataset.code);$('[name="description"]').val(b.dataset.description);$('[name="percentage"]').val(b.dataset.percentage);$('[name="parent_id"]').val(b.dataset.parentId);$('#master-active').prop('checked',b.dataset.active==='1');$('#master-modal-title').text('Edit {{ str($module['title'])->singular() }}');modal.show();});
$('#master-form').validate({errorElement:'span',errorClass:'invalid-feedback',highlight:e=>$(e).addClass('is-invalid'),unhighlight:e=>$(e).removeClass('is-invalid'),submitHandler:function(form){submitFormUsingAjax(form,{reset:false,table:'#masters-table',onSuccess:()=>modal.hide()});}});
$(document).on('click','.delete-master',function(){const url=this.dataset.url;Swal.fire({title:'Delete this record?',icon:'warning',showCancelButton:true,confirmButtonText:'Delete'}).then(r=>{if(!r.isConfirmed)return;$.ajax({url,type:'DELETE'}).done(res=>{Swal.fire('Deleted',res.message,'success');table.ajax.reload(null,false);}).fail(xhr=>Swal.fire('Unable to delete',xhr.responseJSON?.message||'Please try again.','error'));});});
$('#download-master-pdf').on('click',()=>{const params=new URLSearchParams({status:$('#master-status-filter').val()||'',parent_id:$('#master-parent-filter').val()||'',search:table.search()||''});window.location.href=@json(route('admin.masters.pdf',$moduleKey))+'?'+params;});
});
</script>
@endpush
