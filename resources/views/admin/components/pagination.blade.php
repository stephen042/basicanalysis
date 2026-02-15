{{-- Admin Pagination Component --}}
<div class="d-flex justify-content-between align-items-center mt-3">
    <div>
        @if($paginator->total() > 0)
            <small class="text-muted">
                Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
            </small>
        @else
            <small class="text-muted">No results found</small>
        @endif
    </div>
    
    <div>
        {{ $paginator->appends(request()->query())->links() }}
    </div>
</div>
