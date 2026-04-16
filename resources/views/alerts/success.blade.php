@if (session($key ?? 'status'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session($key ?? 'status') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <i class="tim-icons icon-simple-remove"></i>
    </button>
</div>
@endif
