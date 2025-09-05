@extends('admin.layouts.app')

@section('title', 'Attribute Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Attribute Details: {{ $attribute->name }}</h3>
                    <div>
                        <a href="{{ route('admin.attributes.values', $attribute->id) }}" class="btn btn-success">
                            <i class="fas fa-list mr-1"></i>Manage Values
                        </a>
                        <a href="{{ route('admin.attributes.edit', $attribute->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit mr-1"></i>Edit Attribute
                        </a>
                        <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-arrow-left mr-1"></i>Back to Attributes
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="200">Attribute Name</th>
                                    <td>{{ $attribute->name }}</td>
                                </tr>
                                <tr>
                                    <th>Category</th>
                                    <td><span class="badge badge-secondary">{{ $attribute->category->name }}</span></td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td><span class="badge badge-info">{{ ucfirst($attribute->type) }}</span></td>
                                </tr>
                                <tr>
                                    <th>Required</th>
                                    <td>
                                        @if($attribute->required)
                                            <span class="badge badge-danger">Required</span>
                                        @else
                                            <span class="badge badge-light">Optional</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Filterable</th>
                                    <td>
                                        @if($attribute->filterable)
                                            <span class="badge badge-success">Yes</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Sort Order</th>
                                    <td>{{ $attribute->sort_order }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($attribute->status)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created</th>
                                    <td>{{ $attribute->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Last Updated</th>
                                    <td>{{ $attribute->updated_at->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            @if($attribute->type === 'select' && $attribute->attributeValues->count() > 0)
                                <h6>Predefined Values ({{ $attribute->attributeValues->count() }})</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>Value</th>
                                                <th>Display Name</th>
                                                <th>Status</th>
                                                <th>Sort Order</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($attribute->attributeValues as $value)
                                            <tr>
                                                <td><code>{{ $value->value }}</code></td>
                                                <td>{{ $value->display_value ?: '-' }}</td>
                                                <td>
                                                    @if($value->status)
                                                        <span class="badge badge-success">Active</span>
                                                    @else
                                                        <span class="badge badge-secondary">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>{{ $value->sort_order }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <a href="{{ route('admin.attributes.values', $attribute->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus mr-1"></i>Manage Values
                                </a>
                            @elseif($attribute->type === 'select')
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle mr-1"></i>No Values Defined</h6>
                                    <p class="mb-2">This is a select-type attribute but no predefined values have been added yet.</p>
                                    <a href="{{ route('admin.attributes.values', $attribute->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus mr-1"></i>Add Values
                                    </a>
                                </div>
                            @else
                                <div class="alert alert-secondary">
                                    <h6><i class="fas fa-info-circle mr-1"></i>Dynamic Input</h6>
                                    <p class="mb-0">This attribute accepts {{ $attribute->type }} input directly from users when creating products.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection