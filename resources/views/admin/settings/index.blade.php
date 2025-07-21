@extends('admin.layouts.app')

@section('title', 'Settings - MultiShop Admin')
@section('page-title', 'Website Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Settings</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-tabs">
                <div class="card-header p-0 pt-1">
                    <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="general-tab" data-toggle="pill" href="#general" role="tab" aria-controls="general" aria-selected="true">General</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="homepage-tab" data-toggle="pill" href="#homepage" role="tab" aria-controls="homepage" aria-selected="false">Homepage</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="contact-tab" data-toggle="pill" href="#contact" role="tab" aria-controls="contact" aria-selected="false">Contact Info</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="social-tab" data-toggle="pill" href="#social" role="tab" aria-controls="social" aria-selected="false">Social Media</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="tab-content" id="custom-tabs-one-tabContent">
                            <!-- General Settings -->
                            <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="site_name">Site Name</label>
                                            <input type="text" class="form-control" id="site_name" name="site_name" value="MultiShop">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="site_tagline">Site Tagline</label>
                                            <input type="text" class="form-control" id="site_tagline" name="site_tagline" value="Online Shop Website">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="site_description">Site Description</label>
                                    <textarea class="form-control" id="site_description" name="site_description" rows="3">Your one-stop shop for all your fashion needs</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="currency">Default Currency</label>
                                            <select class="form-control" id="currency" name="currency">
                                                <option value="USD" selected>USD ($)</option>
                                                <option value="EUR">EUR (€)</option>
                                                <option value="GBP">GBP (£)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="timezone">Timezone</label>
                                            <select class="form-control" id="timezone" name="timezone">
                                                <option value="UTC" selected>UTC</option>
                                                <option value="America/New_York">Eastern Time</option>
                                                <option value="America/Chicago">Central Time</option>
                                                <option value="America/Los_Angeles">Pacific Time</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="logo">Site Logo</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="logo" name="logo" accept="image/*">
                                            <label class="custom-file-label" for="logo">Choose file</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Homepage Settings -->
                            <div class="tab-pane fade" id="homepage" role="tabpanel" aria-labelledby="homepage-tab">
                                <div class="form-group">
                                    <label for="hero_title">Hero Section Title</label>
                                    <input type="text" class="form-control" id="hero_title" name="hero_title" value="Welcome to MultiShop">
                                </div>

                                <div class="form-group">
                                    <label for="hero_subtitle">Hero Section Subtitle</label>
                                    <textarea class="form-control" id="hero_subtitle" name="hero_subtitle" rows="2">Discover amazing products at unbeatable prices</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="featured_categories">Featured Categories (Max 4)</label>
                                            <input type="number" class="form-control" id="featured_categories" name="featured_categories" value="4" min="1" max="8">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="featured_products">Featured Products (Max 8)</label>
                                            <input type="number" class="form-control" id="featured_products" name="featured_products" value="8" min="1" max="12">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="show_carousel" name="show_carousel" checked>
                                        <label class="custom-control-label" for="show_carousel">Show Homepage Carousel</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="show_vendors" name="show_vendors" checked>
                                        <label class="custom-control-label" for="show_vendors">Show Vendor Section</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Info -->
                            <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="contact_email">Contact Email</label>
                                            <input type="email" class="form-control" id="contact_email" name="contact_email" value="info@multishop.com">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="contact_phone">Contact Phone</label>
                                            <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="+012 345 6789">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="contact_address">Contact Address</label>
                                    <textarea class="form-control" id="contact_address" name="contact_address" rows="3">123 Street, New York, USA</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="business_hours">Business Hours</label>
                                            <input type="text" class="form-control" id="business_hours" name="business_hours" value="Mon - Fri: 9:00 AM - 6:00 PM">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="support_hours">Support Hours</label>
                                            <input type="text" class="form-control" id="support_hours" name="support_hours" value="24/7 Customer Support">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Social Media -->
                            <div class="tab-pane fade" id="social" role="tabpanel" aria-labelledby="social-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="facebook_url">Facebook URL</label>
                                            <input type="url" class="form-control" id="facebook_url" name="facebook_url" placeholder="https://facebook.com/yourpage">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="twitter_url">Twitter URL</label>
                                            <input type="url" class="form-control" id="twitter_url" name="twitter_url" placeholder="https://twitter.com/yourhandle">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="instagram_url">Instagram URL</label>
                                            <input type="url" class="form-control" id="instagram_url" name="instagram_url" placeholder="https://instagram.com/yourhandle">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="linkedin_url">LinkedIn URL</label>
                                            <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" placeholder="https://linkedin.com/company/yourcompany">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="show_social_links" name="show_social_links" checked>
                                        <label class="custom-control-label" for="show_social_links">Show Social Media Links in Footer</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                            <button type="reset" class="btn btn-secondary">Reset</button>
                        </div>
                    </form>
                </div>
                <!-- /.card -->
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
});
</script>
@endpush