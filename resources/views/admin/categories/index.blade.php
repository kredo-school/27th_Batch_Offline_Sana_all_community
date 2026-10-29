@extends('layouts.app')

@section('title', 'Admin Categories')

@section('content')

 <style>
        .admin-dashboard {
            --admin-ink: #173d3d;
            --admin-muted: #647b79;
            --admin-line: #d8e7e4;
            --admin-teal: #35c6c5;
            display: grid;
            grid-template-columns: 210px minmax(0, 1fr);
            min-height: calc(100vh - 74px);
            color: var(--admin-ink);
            background: #f5f8f7;
        }

        .admin-sidebar {
            padding: 24px 14px;
            border-right: 1px solid var(--admin-line);
            background: #fff;
        }

        .admin-sidebar-title {
            margin: 0 10px 16px;
            color: var(--admin-muted);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .admin-nav {
            display: grid;
            gap: 4px;
        }

        .admin-nav-link,
        .admin-nav-disabled {
            display: flex;
            align-items: center;
            gap: 11px;
            min-height: 40px;
            padding: 0 11px;
            border-radius: 5px;
            color: var(--admin-ink);
            font-size: 14px;
            text-decoration: none;
        }

        .admin-nav-link i,
        .admin-nav-disabled i {
            width: 17px;
            color: #668380;
            text-align: center;
        }

        .admin-nav-link.active {
            font-weight: 700;
        }

        .admin-main {
            min-width: 0;
            padding: 28px clamp(16px, 3vw, 42px) 36px;
        }

        .admin-heading {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }

        .admin-eyebrow {
            margin: 0 0 5px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .admin-heading h1 {
            margin: 0;
            font-size: 25px;
            font-weight: 700;
        }

        .admin-count {
            color: var(--admin-muted);
            font-size: 13px;
        }

        .admin-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 14px;
            padding: 12px;
            border: 1px solid var(--admin-line);
            border-radius: 6px;
            background: #fff;
        }

        .admin-search {
            display: flex;
            flex: 1 1 340px;
            max-width: 540px;
        }

        .admin-search input,
        .admin-sort select {
            min-height: 40px;
            border: 1px solid #cbdedb;
            color: var(--admin-ink);
            background: #fff;
        }

        .admin-search input {
            min-width: 0;
            flex: 1;
            padding: 0 12px;
            border-radius: 4px 0 0 4px;
        }

        .admin-search button {
            width: 44px;
            border: 0;
            border-radius: 0 4px 4px 0;
            color: #fff;
        }

        .admin-sort {
            display: flex;
            align-items: center;
            gap: 9px;
            color: var(--admin-muted);
            font-size: 13px;
        }

        .admin-sort select {
            min-width: 150px;
            padding: 0 30px 0 10px;
            border-radius: 4px;
        }

        .admin-table-wrap {
            overflow-x: auto;
            border: 1px solid #badbd5;
            border-radius: 5px;
            background: #fff;
        }

        .admin-table {
            min-width: 900px;
            margin: 0;
            color: var(--admin-ink);
            font-size: 13px;
        }

        .admin-table thead th {
            padding: 12px 13px;
            background: var(--admin-teal);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .admin-table tbody td {
            padding: 10px 13px;
            border-color: #c9e8df;
            vertical-align: middle;
            white-space: nowrap;
        }


        .admin-avatar {
            display: grid;
            width: 34px;
            height: 34px;
            place-items: center;
            overflow: hidden;
            border: 1px solid #badbd5;
            border-radius: 50%;
            color: #126b6b;
            background: #fff;
            font-weight: 700;
        }

        .admin-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .admin-badge {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            padding: 0 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }

        .admin-empty {
            padding: 34px 12px !important;
            color: var(--admin-muted);
            text-align: center;
        }

        .admin-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-top: 16px;
            color: var(--admin-muted);
            font-size: 13px;
        }

        .admin-pagination nav p {
            margin: 0;
        }

        @media (max-width: 760px) {
            .admin-dashboard {
                grid-template-columns: minmax(0, 1fr);
            }

            .admin-sidebar {
                padding: 10px 12px;
                border-right: 0;
                border-bottom: 1px solid var(--admin-line);
            }

            .admin-sidebar-title {
                margin-bottom: 8px;
            }

            .admin-nav {
                display: flex;
                overflow-x: auto;
            }

            .admin-nav-link,
            .admin-nav-disabled {
                flex: 0 0 auto;
            }

            .admin-main {
                padding: 22px 12px;
            }

            .admin-toolbar,
            .admin-pagination {
                align-items: stretch;
                flex-direction: column;
            }

            .admin-search {
                max-width: none;
                flex-basis: auto;
            }

            .admin-sort {
                justify-content: space-between;
            }
        }
    </style>


<div class="admin-dashboard">
    @include('admin.partials.sidebar')

    <section class="admin-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <p class="admin-eyebrow">Admin console</p>
                <h1>Categories</h1>
            </div>

            <a href="{{ route('admin.categories.create') }}" class="btn btn-success">
                Add Category
            </a>
        </div>

        <table class="table table-hover align-middle bg-white border">
            <thead class="table-success">
                <tr>
                    <th>ID</th>
                    <th>NAME</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                @forelse ($categories as $category)
            <tr>
                <td>{{ $category->id }}</td>
                <td>{{ $category->name }}</td>
                <td>
                    {{-- Edit --}}
                    <a href="{{ route('admin.categories.edit', $category->id) }}"
                        class="btn btn-sm btn-primary">
                        Edit
                    </a>

                    {{-- Delete --}}
                    <button type="button"
                        class="btn btn-sm btn-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteCategoryModal{{ $category->id }}">
                        Delete
                    </button>

                    {{-- Delete Confirmation Modal --}}
                    <div class="modal fade"
                        id="deleteCategoryModal{{ $category->id }}"
                        tabindex="-1"
                        aria-labelledby="deleteCategoryModalLabel{{ $category->id }}"
                        aria-hidden="true">

                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title"
                                        id="deleteCategoryModalLabel{{ $category->id }}">
                                        Delete Category
                                    </h5>

                                    <button type="button"
                                        class="btn-close"
                                        data-bs-dismiss="modal"
                                        aria-label="Close">
                                    </button>
                                </div>

                                <div class="modal-body">
                                    Are you sure you want to delete
                                    <strong>{{ $category->name }}</strong>?
                                </div>

                                <div class="modal-footer">

                                    <button type="button"
                                        class="btn btn-outline-warning"
                                        data-bs-dismiss="modal">
                                        Cancel
                                    </button>

                                    <form action="{{ route('admin.categories.destroy', $category->id) }}"
                                        method="POST"
                                        class="d-inline">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                            class="btn btn-danger">
                                            Delete
                                        </button>

                                    </form>

                                </div>

                            </div>
                        </div>
                    </div>

                </td>
            </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-secondary py-4">No categories yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>

@endsection