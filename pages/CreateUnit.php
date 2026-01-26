<div class="nxl-content">
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Building Create</h5>
            </div>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex d-md-none">
                    <a href="javascript:void(0)" class="page-header-right-close-toggle">
                        <i class="feather-arrow-left me-2"></i>
                        <span>Back</span>
                    </a>
                </div>
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

                    <a href="admin.php?page=building" class="btn btn-primary">
                        <i class="feather-arrow me-2"></i>
                        <span>Back</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- [ page-header ] end -->

    <!-- [ Main Content ] start -->
    <div class="main-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <form action="" method="">
                        <div class="card-body general-info">
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="fullnameInput" class="fw-semibold">Name : </label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-user"></i></div>
                                        <input type="text" name="name" class="form-control" id="fullnameInput" placeholder="Name">
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label class="fw-semibold">Building Type : </label>
                                </div>
                                <div class="col-lg-8">
                                    <select class="form-control" name="type" data-select2-selector="country">
                                        <option selected disabled>Select Building Type</option>
                                        <option data-country="af" value="1">type One</option>
                                        <option data-country="ax" value="2">Type Two</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="addressInput" class="fw-semibold">Address: </label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-map-pin"></i></div>
                                        <textarea class="form-control" name="address" id="addressInput" cols="30" rows="3"
                                            placeholder="Address"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="descriptionInput" class="fw-semibold">Description: </label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-type"></i></div>
                                        <textarea class="form-control" name="description" id="descriptionInput" cols="30" rows="5"
                                            placeholder="Description"></textarea>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="companyInput" class="fw-semibold">Image : </label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-photo"></i></div>
                                        <input type="file" name="image" class="form-control" id="companyInput" >
                                    </div>
                                </div>
                            </div>
                        
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="websiteInput" class="fw-semibold">Location : </label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-link"></i></div>
                                        <input type="url" name="location" class="form-control" id="websiteInput" placeholder="Location">
                                    </div>
                                </div>
                            </div>

                             <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    
                                </div>
                                <div class="col-lg-8">
                                    <button type="submit" name="btn" class="btn btn-success ">Save</button>
                                </div>
                            </div>
                            
                        </div>
                    </form>
                </div>      
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>