@extends('layouts.admin')
@section('content')
<style>
    .table-striped th:nth-child(1),
    .table-striped td:nth-child(1) {
        width: 100px;
    }

    .table-striped th:nth-child(2),
    .table-striped td:nth-child(2) {
        width: 250px;
    }
</style>
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3><i class="icon-package me-2"></i>Products</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li>
                    <a href="{{route('admin.index')}}">
                        <div class="text-tiny"><i class="icon-home me-1"></i>Dashboard</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
                <li>
                    <div class="text-tiny"><i class="icon-package me-1"></i>Products</div>
                </li>
            </ul>
        </div>

        <div class="wg-box">
            <div class="flex items-center justify-between gap10 flex-wrap">
                <div class="wg-filter flex-grow">
                    <form class="form-search" method="GET" action="{{route('admin.products')}}" id="searchForm">
                        <fieldset class="name">
                            <input type="text" placeholder="Search products, SKU, category, brand..." class="" name="name" tabindex="2" value="{{request('name')}}" aria-required="false" id="searchInput">
                        </fieldset>
                        <div class="button-submit">
                            <button class="" type="submit" title="Search"><i class="icon-search"></i></button>
                        </div>
                        @if(request('name'))
                        <div class="button-submit ms-1">
                            <a href="{{route('admin.products')}}" class="btn btn-sm btn-outline-secondary" title="Clear Search">
                                <i class="icon-x"></i>
                            </a>
                        </div>
                        @endif
                    </form>
                    @if(request('name'))
                    <div class="mt-2">
                        <small class="text-muted"><i class="icon-search me-1"></i>Search results for: "<strong>{{request('name')}}</strong>" ({{$products->total()}} found)</small>
                    </div>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-secondary" onclick="printProducts()" title="Print Products List">
                        <i class="icon-printer"></i> Print
                    </button>
                    <a class="btn btn-primary" href="{{route('admin.product.add')}}" title="Add New Product">
                        <i class="icon-plus"></i> Add New
                    </a>
                </div>
            </div>
            <!-- Print Header (hidden by default, shown only when printing) -->
            <div class="print-header">
                <h2><i class="icon-package"></i> HOMMSS Products List</h2>
                <p>Generated on: <span id="printDate"></span></p>
            </div>

            <div class="table-responsive">
                @if(Session::has('status'))
                <p class="alert alert-success"><i class="icon-check-circle me-2"></i>{{Session::get('status')}}</p>
                @endif
                <table class="table table-striped table-bordered" id="productsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Featured</th>
                            <th>Stock Status</th>
                            <th>Quantity</th>
                            <th class="no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($products->count() > 0)
                        @foreach ($products as $product)
                        <tr>
                            <td>{{$product->id}}</td>
                            <td class="pname">
                                <div class="image">
                                    <img src="{{asset('uploads/products/thumbnails')}}/{{$product->image}}" alt="" class="image">
                                </div>
                                <div class="name">
                                    <a href="#" class="body-title-2">{{$product->name}}</a>
                                    <div class="text-tiny mt-3">{{$product->slug}}</div>
                                </div>
                            </td>
                            <td>₱{{$product->regular_price}}</td>
                            <td>{{$product->SKU}}</td>
                            <td>{{$product->category->name}}</td>
                            <td>{{$product->brand->name}}</td>
                            <td>
                                @if($product->featured == 1)
                                <span class="badge bg-success">Yes</span>
                                @else
                                <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td>
                                @if($product->stock_status == 'instock')
                                <span class="badge bg-success">In Stock</span>
                                @else
                                <span class="badge bg-danger">Out of Stock</span>
                                @endif
                            </td>
                            <td><span class="badge bg-info">{{$product->quantity}}</span></td>
                            <td class="no-print">
                                <div class="list-icon-function">
                                    <a href="#" title="View Product" class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                    <a href="{{route('admin.product.edit',['id'=>$product->id])}}" title="Edit Product" class="btn btn-sm btn-outline-success">
                                        Edit
                                    </a>
                                    <form action="{{route('admin.product.delete',['id'=>$product->id])}}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger delete" title="Delete Product">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                @if(request('name'))
                                <div class="alert alert-info">
                                    <i class="icon-search me-2"></i>
                                    No products found for "<strong>{{request('name')}}</strong>"
                                    <br>
                                    <small class="text-muted">Try searching with different keywords or <a href="{{route('admin.products')}}">view all products</a></small>
                                </div>
                                @else
                                <div class="alert alert-warning">
                                    <i class="icon-package me-2"></i>
                                    No products found. <a href="{{route('admin.product.add')}}">Add your first product</a>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="divider"></div>

            <!-- Products Summary -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex gap-3">
                    <small class="text-muted">
                        <i class="icon-info me-1"></i>
                        Showing {{$products->firstItem()}} to {{$products->lastItem()}} of {{$products->total()}} products
                    </small>
                    @if(request('name'))
                    <small class="text-info">
                        <i class="icon-filter me-1"></i>
                        Filtered results
                    </small>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <small class="text-muted">
                        <i class="icon-layers me-1"></i>
                        Total: <strong>{{$products->total()}}</strong> products
                    </small>
                </div>
            </div>

            <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                {{$products->links('pagination::bootstrap-5')}}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Print Styles */
    @media print {
        .no-print {
            display: none !important;
        }

        .wg-filter,
        .tf-button,
        .btn,
        .pagination {
            display: none !important;
        }

        .main-content {
            padding: 0 !important;
        }

        .wg-box {
            border: none !important;
            box-shadow: none !important;
        }

        .table {
            border-collapse: collapse !important;
        }

        .table th,
        .table td {
            border: 1px solid #000 !important;
            padding: 8px !important;
        }

        .table thead th {
            background-color: #f8f9fa !important;
            font-weight: bold !important;
        }

        .badge {
            border: 1px solid #000 !important;
            padding: 2px 6px !important;
        }

        /* Print header */
        .print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .print-date {
            display: block !important;
            text-align: right;
            margin-bottom: 10px;
            font-size: 12px;
        }
    }

    .print-header,
    .print-date {
        display: none;
    }

    /* Enhanced search styling */
    .form-search {
        position: relative;
        display: flex;
        align-items: center;
        background-color: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        overflow: hidden;
    }

    .form-search input {
        border: none;
        background: transparent;
        padding: 12px 16px;
        flex: 1;
        font-size: 14px;
    }

    .form-search input:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(34, 117, 252, 0.25);
    }

    .form-search .button-submit button {
        border: none !important;
        background: transparent !important;
        color: #000 !important;
        padding: 12px 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        border-radius: 0 6px 6px 0;
    }

    .form-search .button-submit button:hover {
        background: #f8f9fa !important;
        transform: translateY(-1px);
        color: #000 !important;
    }

    /* Force search icon to be black */
    .form-search .button-submit button i {
        color: #000 !important;
        font-size: 16px;
    }

    .form-search .button-submit button:hover i {
        color: #000 !important;
    }

    .form-search .button-submit a {
        border: none;
        background: #dc3545;
        color: white;
        padding: 12px 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }

    .form-search .button-submit a:hover {
        background: #c82333;
        transform: translateY(-1px);
        color: white;
    }

    /* Enhanced badges */
    .badge {
        font-size: 11px;
        font-weight: 500;
        padding: 4px 8px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
    }

    .badge i {
        font-size: 10px;
    }

    /* Enhanced action buttons */
    .list-icon-function {
        display: flex;
        gap: 4px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .list-icon-function .btn {
        font-size: 12px;
        padding: 6px 12px;
        margin: 2px;
        border-radius: 5px;
        transition: all 0.3s ease;
        text-decoration: none;
        border: 1px solid;
    }

    .list-icon-function .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        text-decoration: none;
    }

    .list-icon-function .btn-outline-primary {
        color: #0d6efd;
        border-color: #0d6efd;
    }

    .list-icon-function .btn-outline-primary:hover {
        background-color: #0d6efd;
        color: white;
    }

    .list-icon-function .btn-outline-success {
        color: #198754;
        border-color: #198754;
    }

    .list-icon-function .btn-outline-success:hover {
        background-color: #198754;
        color: white;
    }

    .list-icon-function .btn-outline-danger {
        color: #dc3545;
        border-color: #dc3545;
    }

    .list-icon-function .btn-outline-danger:hover {
        background-color: #dc3545;
        color: white;
    }

    /* Fix table row height consistency - Force override */
    #productsTable tbody tr {
        height: 80px !important;
        min-height: 80px !important;
        max-height: 80px !important;
        vertical-align: middle !important;
    }

    #productsTable tbody td {
        vertical-align: middle !important;
        padding: 12px 16px !important;
        height: 80px !important;
        line-height: 1.4 !important;
    }

    /* Force table layout */
    #productsTable {
        table-layout: fixed !important;
    }

    #productsTable tbody {
        display: table-row-group !important;
    }

    /* Fix product image sizing */
    .pname .image {
        width: 50px !important;
        height: 50px !important;
        min-width: 50px !important;
        min-height: 50px !important;
        max-width: 50px !important;
        max-height: 50px !important;
        flex-shrink: 0 !important;
        overflow: hidden !important;
        border-radius: 6px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        border: 1px solid #e0e0e0 !important;
        background-color: #f8f9fa !important;
        position: relative !important;
    }

    .pname .image img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        border-radius: 4px !important;
        display: block !important;
    }

    /* Handle missing images */
    .pname .image:empty::before,
    .pname .image img[src=""]::before,
    .pname .image img:not([src])::before {
        content: "📦" !important;
        font-size: 20px !important;
        color: #6c757d !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        height: 100% !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        background-color: #f8f9fa !important;
    }

    /* Hide broken images */
    .pname .image img[src=""],
    .pname .image img:not([src]) {
        display: none !important;
    }

    /* Fix product name container */
    .pname {
        display: flex !important;
        align-items: center !important;
        gap: 15px !important;
        min-height: 50px !important;
        height: 50px !important;
        max-height: 50px !important;
        width: 100% !important;
        overflow: hidden !important;
    }

    .pname .name {
        flex: 1 !important;
        min-width: 0 !important;
        height: 50px !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        overflow: hidden !important;
    }

    .pname .name .body-title-2 {
        font-weight: 500 !important;
        color: #333 !important;
        text-decoration: none !important;
        display: block !important;
        margin-bottom: 2px !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        font-size: 14px !important;
        line-height: 1.2 !important;
    }

    .pname .name .text-tiny {
        color: #666 !important;
        font-size: 12px !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        line-height: 1.2 !important;
        margin: 0 !important;
    }

    /* Handle missing product names */
    .pname .name .body-title-2:empty::before {
        content: "Untitled Product" !important;
        color: #999 !important;
        font-style: italic !important;
    }

    .pname .name .text-tiny:empty::before {
        content: "no-slug" !important;
        color: #999 !important;
        font-style: italic !important;
    }

    /* Ensure badges have consistent height */
    .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 500;
        border-radius: 5px;
        white-space: nowrap;
    }

    /* Fix action buttons container */
    .list-icon-function {
        min-height: 32px;
        align-items: center;
    }

    /* Force consistent cell content alignment */
    #productsTable tbody td * {
        vertical-align: middle !important;
    }

    /* Specific fixes for different cell types */
    #productsTable tbody td:first-child {
        text-align: center !important;
        font-weight: 500 !important;
    }

    #productsTable tbody td:nth-child(3),
    #productsTable tbody td:nth-child(4),
    #productsTable tbody td:nth-child(5),
    #productsTable tbody td:nth-child(6) {
        text-align: left !important;
        vertical-align: middle !important;
    }

    #productsTable tbody td:nth-child(7),
    #productsTable tbody td:nth-child(8),
    #productsTable tbody td:nth-child(9),
    #productsTable tbody td:nth-child(10) {
        text-align: center !important;
        vertical-align: middle !important;
    }

    /* Ensure badges don't affect row height */
    #productsTable .badge {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        vertical-align: middle !important;
    }

    /* Ensure product name container doesn't expand */
    #productsTable .pname {
        height: 50px !important;
        max-height: 50px !important;
        overflow: hidden !important;
    }

    /* Handle empty cells and missing data */
    #productsTable tbody td:empty::before {
        content: "-" !important;
        color: #999 !important;
        font-style: italic !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        height: 100% !important;
    }

    /* Ensure all text cells have minimum content height */
    #productsTable tbody td:nth-child(3),
    #productsTable tbody td:nth-child(4),
    #productsTable tbody td:nth-child(5),
    #productsTable tbody td:nth-child(6) {
        min-height: 80px !important;
        display: table-cell !important;
        vertical-align: middle !important;
    }

    /* Force all cells to maintain table structure */
    #productsTable tbody td {
        display: table-cell !important;
        box-sizing: border-box !important;
    }

    /* Prevent any content from breaking row height */
    #productsTable tbody td > * {
        max-height: 60px !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
    }

    /* Exception for badges and buttons which should be smaller */
    #productsTable tbody td .badge,
    #productsTable tbody td .btn,
    #productsTable tbody td .list-icon-function {
        max-height: none !important;
    }

    /* Consistent button styling */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.3s ease;
        border: 1px solid transparent;
        text-decoration: none;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        text-decoration: none;
    }

    .btn-primary {
        background-color: var(--hommss-blue) !important;
        border-color: var(--hommss-blue) !important;
        color: white !important;
    }

    .btn-primary:hover {
        background-color: var(--hommss-blue-hover) !important;
        border-color: var(--hommss-blue-hover) !important;
        color: white !important;
    }

    .btn-secondary {
        background-color: #6c757d !important;
        border-color: #6c757d !important;
        color: white !important;
    }

    .btn-secondary:hover {
        background-color: #5a6268 !important;
        border-color: #545b62 !important;
        color: white !important;
    }

    .btn i {
        font-size: 14px;
    }

    /* Force print button icon to be white */
    .btn-secondary i {
        color: white !important;
    }

    .btn-secondary:hover i {
        color: white !important;
    }

    /* Force primary button icon to be white */
    .btn-primary i {
        color: white !important;
    }

    .btn-primary:hover i {
        color: white !important;
    }

    /* Search form improvements */
    .form-search {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: 1px solid #dee2e6;
    }

    .form-search:focus-within {
        box-shadow: 0 0 0 3px rgba(34, 117, 252, 0.1);
        border-color: var(--hommss-blue);
    }



    /* Ensure button text and icons are properly aligned */
    .d-flex .btn {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
    }

    .d-flex .btn i {
        margin-right: 0 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $(function() {
        // Delete confirmation
        $('.delete').on('click', function(e) {
            e.preventDefault();
            var form = $(this).closest('form');
            swal({
                title: "Are you sure?",
                text: "You want to delete this product? This action cannot be undone.",
                type: "warning",
                buttons: ["Cancel", "Delete"],
                confirmButtonColor: '#dc3545',
                dangerMode: true
            }).then(function(result) {
                if (result) {
                    form.submit();
                }
            });
        });

        // Enhanced search functionality
        $('#searchInput').on('keyup', function(e) {
            if (e.key === 'Enter') {
                $('#searchForm').submit();
            }
        });

        // Auto-focus search input
        $('#searchInput').focus();

        // Search suggestions (if needed)
        $('#searchInput').on('input', function() {
            var value = $(this).val();
            if (value.length > 2) {
                // Add search suggestions here if needed
            }
        });
    });

    // Print functionality
    function printProducts() {
        // Add print header
        var printHeader = `
            <div class="print-header">
                <h2><i class="icon-package"></i> HOMMSS Products List</h2>
                <p>Generated on: ${new Date().toLocaleDateString()} at ${new Date().toLocaleTimeString()}</p>
            </div>
        `;

        // Get the table content
        var tableContent = document.getElementById('productsTable').outerHTML;

        // Create print window
        var printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>HOMMSS Products List</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .print-header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
                    .print-header h2 { margin: 0; color: #2275fc; }
                    .print-header p { margin: 5px 0 0 0; font-size: 14px; color: #666; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid #000; padding: 8px; text-align: left; }
                    th { background-color: #f8f9fa; font-weight: bold; }
                    .badge { border: 1px solid #000; padding: 2px 6px; border-radius: 3px; font-size: 11px; }
                    .bg-success { background-color: #d4edda; }
                    .bg-danger { background-color: #f8d7da; }
                    .bg-info { background-color: #d1ecf1; }
                    .bg-secondary { background-color: #e2e3e5; }
                    .no-print { display: none; }
                    img { max-width: 50px; max-height: 50px; }
                </style>
            </head>
            <body>
                ${printHeader}
                ${tableContent}
                <div style="margin-top: 20px; text-align: center; font-size: 12px; color: #666;">
                    <p>Total Products: ${document.querySelectorAll('#productsTable tbody tr').length - 1}</p>
                    <p>© ${new Date().getFullYear()} HOMMSS - Products Management System</p>
                </div>
            </body>
            </html>
        `);

        printWindow.document.close();
        printWindow.focus();

        // Wait for content to load then print
        setTimeout(function() {
            printWindow.print();
            printWindow.close();
        }, 250);
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+P for print
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            printProducts();
        }

        // Ctrl+F for search focus
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            document.getElementById('searchInput').focus();
        }
    });
</script>
@endpush