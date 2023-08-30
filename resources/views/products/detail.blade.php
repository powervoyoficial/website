@include('layouts.app')


@include('layouts.header')
@php 
    $cityToCountry= file_get_contents(asset('tz-cities-to-countries.json'));
    $cityToCountry=json_decode($cityToCountry,true);
     $countriesJs=array();
    foreach($cityToCountry as $key=>$value){
        $countriesJs[$key]=$value;
     }
@endphp

<div class="rentalcar-detail-page pt-5 product-detail-page mb-4">

    <div class="container position-relative">

        <div class="car-detail-inner">

            <div class="car-del-top-section">

                <div class="row" id="product-detail">

                </div>

                <div class="hidden-inputs">
                    <input type="hidden" name="vendor_id" id="vendor_id" value="">
                    <input type="hidden" name="vendor_name" id="vendor_name" value="">
                    <input type="hidden" name="vendor_location" id="vendor_location" value="">
                    <input type="hidden" name="vendor_latitude" id="vendor_latitude" value="">
                    <input type="hidden" name="vendor_longitude" id="vendor_longitude" value="">
                    <input type="hidden" name="vendor_image" id="vendor_image" value="">
                </div>
            </div>

            <div class="py-2 mb-3 rental-detailed-ratings-and-reviews mt-5">
                <div class="row">
                    <div class="rental-review col-md-8">
                        <div class="main-specification mb-3"></div>
                        <div class="review-inner">
                            <div id="customers_ratings_and_review"></div>
                            <div class="see_all_review_div" style="display:none">
                                <button class="btn btn-primary btn-block btn-sm see_all_reviews">{{trans('lang.see_all_reviews')}}</button>
                            </div>
                            <p class="no_review_fount" style="display:none">{{trans('lang.no_review_found')}}</p>
                        </div>
                    </div>

                    <div class="col-md-4 store-info">

                        <div class="shipping-detail card p-4 mb-4">
                            <div class="shipping-details-bottom-border pb-3">
                                <img class="mr-2" src="{{url('img/Payment.png')}}" alt="">
                                <span>{{trans('lang.safe_payment')}}</span>
                            </div>
                            <div class="shipping-details-bottom-border pb-3">
                                <img class="mr-2" src="{{url('img/money.png')}}" alt="">
                                <span>{{trans('lang.return_policy')}}</span>
                            </div>
                            <div class="shipping-details-bottom-border">
                                <img class="mr-2" src="{{url('img/Genuine.png')}}" alt="">
                                <span>{{trans('lang.authentic_products')}}</span>
                            </div>
                        </div>

                        <div class="seller-info">
                            <div class="d-flex justify-content-between card p-4 mb-4">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex">
                                            <div id="seller-image"></div>
                                            <div class="ml-3">
                                                <span class="vendor_name"></span>
                                                <br>
                                                <span>{{trans('lang.seller_info')}}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-4">
                                        <div class="row d-flex justify-content-between">
                                            <div class="col-6 ">
                                                <div class="d-flex justify-content-center align-items-center review-box">
                                                    <div class="text-center">
                                                        <span class="vendor-total-review"></span>
                                                        <br>
                                                        <span> {{trans('lang.reviews')}}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="d-flex justify-content-center align-items-center review-box">
                                                    <div class="text-center">
                                                        <span class="vendor-total-product"></span>
                                                        <br>
                                                        <span> {{trans('lang.products')}} </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <div class="visit-store">
                                            <a class="store_url btn btn-primary" href="#">
                                                <i class="fa fa-shopping-bag" aria-hidden="true"></i>
                                                <span> {{trans('lang.visit_store')}}</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="more-from-store">
                            <div class="card p-4 mb-4">
                                <div class="more-fromd-flex justify-content-center">
                                    <h3> {{trans('lang.more_from_store')}}</h3>
                                </div>
                                <div class="vendor-products" id="vendor-products"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="py-2 mb-3 related-products mt-4" id="related_products">

            </div>

        </div>

    </div>

</div>

<div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">
    {{trans('lang.Processing')}}
</div>

@include('layouts.footer')

<script src="https://unpkg.com/geofirestore/dist/geofirestore.js"></script>

<script src="https://cdn.firebase.com/libs/geofire/5.0.1/geofire.min.js"></script>
<script type="text/javascript" src="{{asset('vendor/slick/slick.min.js')}}"></script>

<script type="text/javascript">
        cityToCountry = '<?php echo json_encode($countriesJs);?>';

        cityToCountry = JSON.parse(cityToCountry);
        var userTimeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        userCity = userTimeZone.split('/')[1];
        userCountry = cityToCountry[userCity];
    var id = '<?php echo $id; ?>';
    var takeaway = "<?php echo Session::get('takeawayOption'); ?>";

    var productsRef = database.collection('vendor_products').doc(id);

    var firestore = firebase.firestore();
    var geoFirestore = new GeoFirestore(firestore);

    var review_pagesize = 5;
    var review_start = null;
    var specialOfferVendor = [];
    let specialOfferForHour = [];
    var reviewAttributes = {};
    var vendorLongitude = '';
    var vendorLatitude = '';

    var placeholderImageRef = database.collection('settings').doc('placeHolderImage');
    var placeholderImageSrc = '';
    placeholderImageRef.get().then(async function (placeholderImageSnapshots) {
        var placeHolderImageData = placeholderImageSnapshots.data();
        placeholderImageSrc = placeHolderImageData.image;
    });

    var specialOfferRef = database.collection('settings').doc('specialDiscountOffer');
    var enableSpecialOffer = false;
    specialOfferRef.get().then(async function (snapShots) {
        var specialOfferData = snapShots.data();
        if (specialOfferData.isEnable) {
            enableSpecialOffer = specialOfferData.isEnable;
        }
    });

    var taxValue = [];
    //var taxSetting = database.collection('settings').doc("taxSetting");
    var reftaxSetting = database.collection('tax').where('country', '==', userCountry).where('enable','==',true);
            reftaxSetting.get().then(async function (snapshots) {
            if (snapshots.docs.length > 0) {
                snapshots.docs.forEach((val) => {
                    val = val.data();
                    var obj = '';
                    obj = {
                        'country': val.country,
                        'enable': val.enable,
                        'id': val.id,
                        'tax': val.tax,
                        'title': val.title,
                        'type': val.type,
                    }
                    taxValue.push(obj);

                })
            }
        });

    var DeliveryCharge = database.collection('settings').doc('DeliveryCharge');

    var currentCurrency = '';
    var currencyAtRight = false;
    var refCurrency = database.collection('currencies').where('isActive', '==', true);
    var deliveryChargemain = [];
    var decimal_degits = 0;
    refCurrency.get().then(async function (snapshots) {
        var currencyData = snapshots.docs[0].data();
        currentCurrency = currencyData.symbol;
        currencyAtRight = currencyData.symbolAtRight;
        loadcurrency();
        if (currencyData.decimal_degits) {
            decimal_degits = currencyData.decimal_degits;
        }
    });

    var refReviewAttributes = database.collection('review_attributes');
    refReviewAttributes.get().then(async function (snapshots) {
        if (snapshots != undefined) {
            snapshots.forEach((doc) => {
                var data = doc.data();
                reviewAttributes[data.id] = data.title;
            });
        }
    });
    $(document).ready(function () {

        getProductDetail();

        $(document).on('swipe, afterChange', '.nav-slider', function (event, slick, direction) {
            $('.main-slider').slick('slickGoTo', slick.currentSlide);
        });

        $(document).on('click', '.nav-slider .product-image', function () {
            $('.main-slider').slick('slickGoTo', $(this).data('slick-index'));
        });

        $(document).on('click', '.attribute_list .attribute-drp .attribute-selection', function () {
            var product = $(this).parent().parent().parent().data('product');
            getVariantPrice(product);
        });

        $(document).on('click', '.vendor-products .store-product', function () {
            var pid = $(this).data('product-id');
            var view_product_details = "{{ route('productDetail',':id')}}";
            view_product_details = view_product_details.replace(':id', pid);
            window.location.href = view_product_details;
        });

        $(document).on("click", '.add-to-cart', function (event) {

            @guest
                window.location.href = '<?php echo route('login'); ?>';
            return false;
                    @endguest

            var $elem = $(this);

            var id = $(this).attr('data-id');
            //var deliveryCharge = $("#deliveryChargeMain").val();
            var quantity = $('input[name="quantity_' + id + '"]').val();

            if (quantity == 0) {
                alert('{{trans("lang.invalid_qty")}}');
                return false;
            }

            var extra = [];
            var size = $('input[name="size_' + id + '"]:checked').val();

            if (size && size != undefined) {
                var price = parseFloat($('input[name="size_' + id + '"]:checked').attr('data-price'));
            } else {
                var price = parseFloat($('input[name="price_' + id + '"]').val());
            }

            var dis_price = parseFloat($('input[name="dis_price_' + id + '"]').val());
            var item_price = price;

            var stock_quantity = $('#quantity_' + id).val();

            var variant_info = {};
            if ($('#variation_info_' + id).length > 0) {
                var element = $('#variation_info_' + id).find('#variant_price');
                var variant_id = element.attr('data-vid');
                var variant_sku = element.attr('data-vsku');
                var variant_img = element.attr('data-vimg');
                if (variant_img == undefined) {
                    variant_img = placeholderImageSrc;
                }
                var variant_options = $.parseJSON(element.attr('data-vinfo'));
                var variant_price = parseFloat(element.attr('data-vprice'));
                var variant_qty = parseFloat(element.attr('data-vqty'));

                if (quantity > variant_qty && variant_qty != -1) {
                    alert('{{trans("lang.invalid_stock_qty")}}');
                    return false;
                }
                variant_info['variant_id'] = variant_id;
                variant_info['variant_sku'] = variant_sku;
                variant_info['variant_options'] = variant_options;
                variant_info['variant_price'] = variant_price;
                variant_info['variant_qty'] = variant_qty;
                variant_info['variant_image'] = variant_img;
                item_price = variant_price;
                price = variant_price;
            } else {

                if (stock_quantity != undefined && stock_quantity != -1 && parseInt(quantity) > parseInt(stock_quantity)) {

                    alert('{{trans("lang.invalid_stock_qty")}}');
                    return false;
                }
            }


            var category_id = $('input[name="category_id_' + id + '"]').val();
            var restaurant_id = $('input[name="vendor_id"]').val();
            var restaurant_name = $('input[name="name"]').val();
            var restaurant_latitude = $('input[name="vendor_latitude"]').val();
            var restaurant_longitude = $('input[name="vendor_longitude"]').val();

            setCookie('restaurant_longitude', restaurant_longitude, 365);
            setCookie('restaurant_latitude', restaurant_latitude, 365);
            setCookie('deliveryChargemain', JSON.stringify(deliveryChargemain), 356);

            var restaurant_location = $('input[name="vendor_location"]').val();
            var restaurant_image = $('input[name="vendor_image"]').val();
            var delivery_option = $('input[name="delivery_option"]').val();
            var name = $('input[name="name_' + id + '"]').val();
            var veg = $('input[name="veg_' + id + '"]').val();
            price = price * quantity;
            var image = $('input[name="image_' + id + '"]').val();
            var extra_price = 0;
            $('input:checkbox.extra_' + id).each(function () {
                var sThisVal = (this.checked ? $(this).val() : "");
                if (sThisVal != '') {
                    extra_price = parseFloat($(this).attr('data-price')) + extra_price;
                    extra.push(sThisVal);
                }
            });
            var iteam_extra_price = extra_price;
            var total_price = price + extra_price;

            image = image ? image : placeholderImageSrc;

            $.ajax({
                type: 'POST',

                url: "<?php echo route('add-to-cart'); ?>",
                data: {
                    _token: '<?php echo csrf_token(); ?>',
                    restaurant_id: restaurant_id,
                    extra: extra,
                    size: size,
                    id: id,
                    quantity: quantity,
                    stock_quantity: stock_quantity,
                    name: name,
                    price: price,
                    dis_price: dis_price,
                    image: image,
                    extra_price: extra_price,
                    item_price: item_price,
                    restaurant_location: restaurant_location,
                    restaurant_name: restaurant_name,
                    restaurant_image: restaurant_image,
                    veg: veg,
                    taxValue: taxValue,
                    restaurant_latitude: restaurant_latitude,
                    restaurant_longitude: restaurant_longitude,
                    variant_info: variant_info,
                    category_id: category_id,
                    specialOfferForHour: specialOfferForHour,
                    decimal_degits: decimal_degits,

                },
                success: function (data) {
                    data = JSON.parse(data);
                    $('#cart_list').html(data.html);
                    loadcurrency();
                    $('#close_' + id).trigger("click");

                    if ($elem.hasClass('booknow')) {
                        window.location.href = '<?php echo route('checkout'); ?>';
                    } else {
                        alert('{{trans("lang.added_tocart")}}');
                    }
                }
            });
        });


        $(document).on("click", '.remove_item', function (event) {
            var id = $(this).attr('data-id');
            var vendor_id = $(this).attr('data-vendor');
            $.ajax({
                type: 'POST',
                url: "<?php echo route('remove-from-cart'); ?>",
                data: {_token: '<?php echo csrf_token() ?>', vendor_id: vendor_id, id: id},
                success: function (data) {
                    data = JSON.parse(data);
                    $('#cart_list').html(data.html);
                    loadcurrency();
                }
            });
        });

        $(document).on("click", '.count-number-input-cart', function (event) {
            var id = $(this).attr('data-id');
            var vendor_id = $(this).attr('data-vendor');
            var quantity = $('.count_number_' + id).val();
            $.ajax({
                type: 'POST',
                url: "<?php echo route('change-quantity-cart'); ?>",
                data: {
                    _token: '<?php echo csrf_token() ?>',
                    vendor_id: vendor_id,
                    id: id,
                    quantity: quantity,
                    specialOfferForHour: specialOfferForHour
                },
                success: function (data) {
                    data = JSON.parse(data);
                    $('#cart_list').html(data.html);
                    loadcurrency();
                }
            });
        });

        $(document).on("click", '#apply-coupon-code', function (event) {
            var coupon_code = $("#coupon_code").val();
            var vendor_id = $('input[name="vendor_id"]').val();
            var endOfToday = new Date();
            var couponCodeRef = database.collection('coupons').where('code', "==", coupon_code).where('isEnabled', "==", true).where('expiresAt', ">=", endOfToday);

            couponCodeRef.get().then(async function (couponSnapshots) {
                if (couponSnapshots.docs && couponSnapshots.docs.length) {
                    var coupondata = couponSnapshots.docs[0].data();
                    if (coupondata.vendorID != undefined && coupondata.vendorID != '') {
                        if (coupondata.vendorID == vendor_id) {
                            discount = coupondata.discount;
                            coupon_id = coupondata.id;
                            discountType = coupondata.discountType;

                            $.ajax({
                                type: 'POST',
                                url: "<?php echo route('apply-coupon'); ?>",
                                data: {
                                    _token: '<?php echo csrf_token() ?>',
                                    coupon_code: coupon_code,
                                    discount: discount,
                                    discountType: discountType,
                                    coupon_id: coupondata.id,
                                    specialOfferForHour: specialOfferForHour
                                },

                                success: function (data) {
                                    data = JSON.parse(data);
                                    $('#cart_list').html(data.html);
                                    loadcurrency();
                                }
                            });
                        } else {
                            alert("Coupon code is not valid.");
                            $("#coupon_code").val('');
                        }

                    } else {
                        discount = coupondata.discount;
                        discountType = coupondata.discountType;
                        $.ajax({
                            type: 'POST',
                            url: "<?php echo route('apply-coupon'); ?>",
                            data: {
                                _token: '<?php echo csrf_token() ?>',
                                coupon_code: coupon_code,
                                discount: discount,
                                discountType: discountType,
                                coupon_id: coupondata.id,
                                specialOfferForHour: specialOfferForHour
                            },

                            success: function (data) {
                                data = JSON.parse(data);
                                $('#cart_list').html(data.html);
                                loadcurrency();
                            }
                        });
                    }

                } else {
                    alert("Coupon code is not valid.");
                    $("#coupon_code").val('');
                }
            });
        });

        $(document).on("click", '#Other_tip', function (event) {
            $("#tip_amount").val('');
            $("#add_tip_box").show();
        });

        $(document).on("click", '.addon-checkbox', function (event) {
            if ($(this).is(':checked')) {
                $(this).next().addClass('active');
            } else {
                $(this).next().removeClass('active');
            }
        });

        $(document).on("click", '.this_tip', function (event) {
            var this_tip = $(this).val();
            var data = $(this);
            $("#tip_amount").val(this_tip);
            $("#add_tip_box").hide();
            if ((data).is('.tip_checked')) {
                data.removeClass('tip_checked');
                $(this).prop('checked', false);
                tipAmountChange('minus');
            } else {
                $(this).addClass('tip_checked');
                tipAmountChange('plus');
            }
        });
    });
    $(document).on("click", '.this_delivery_option', function (event) {

        var delivery_option = $(this).val();

        var deliveryCharge = $("#deliveryChargeMain").val();


        $.ajax({

            type: 'POST',

            url: "<?php echo route('order-delivery-option'); ?>",

            data: {
                _token: '<?php echo csrf_token() ?>',
                delivery_option: delivery_option,
                deliveryCharge: deliveryCharge
            },

            success: function (data) {

                data = JSON.parse(data);

                $('#cart_list').html(data.html);
                loadcurrency();

            }

        });


        return false;


    });

    function tipAmountChange(type = "plus") {
        var this_tip = $("#tip_amount").val();
        $.ajax({
            type: 'POST',
            url: "<?php echo route('order-tip-add'); ?>",
            data: {_token: '<?php echo csrf_token() ?>', tip: this_tip, type: type},
            success: function (data) {
                data = JSON.parse(data);
                $('#cart_list').html(data.html);
                loadcurrency();
            }
        });
    }

    function getProductDetail() {
        $("#data-table_processing").show();
        productsRef.get().then(async function (snapshots) {
            if (snapshots != undefined) {
                var html = '';
                html = await buildHTML(snapshots);
                jQuery("#data-table_processing").hide();
                if (html != '') {
                    var append_list = document.getElementById('product-detail');
                    append_list.innerHTML = html;
                    slickCarousel();
                }
            }
        });
    }

    function loadcurrency() {
        if (currencyAtRight) {
            jQuery('.currency-symbol-left').hide();
            jQuery('.currency-symbol-right').show();
            jQuery('.currency-symbol-right').text(currentCurrency);
        } else {
            jQuery('.currency-symbol-left').show();
            jQuery('.currency-symbol-right').hide();
            jQuery('.currency-symbol-left').text(currentCurrency);
        }
    }

    async function getVendorDetails(vendorID) {
        var vendorDetailsRef = database.collection('vendors').where('id', "==", vendorID);


        DeliveryCharge.get().then(async function (deliveryChargeSnapshots) {
            deliveryChargemain = deliveryChargeSnapshots.data();

            deliveryCharge = deliveryChargemain.amount;

            $("#deliveryChargeMain").val(deliveryCharge);

            $("#deliveryCharge").val(deliveryCharge);
        });
        //var reftaxSettingData = await taxSetting.get();

        //taxArray = reftaxSettingData.data();
        //taxValue = taxArray;


        vendorDetailsRef.get().then(async function (vendorSnapshots) {

            var vendorDetails = vendorSnapshots.docs[0].data();
            $("#vendor_id").append(vendorDetails.id);
            $("#vendor_title").append(vendorDetails.title);

            $(".vendor_name").val(vendorDetails.title);

            $("#vendor_address").append(vendorDetails.location);

            $("#vendor_location").val(vendorDetails.location);

            $("#vendor_latitude").val(vendorDetails.latitude);

            $("#vendor_longitude").val(vendorDetails.longitude);

            $("#vendor_image").val(vendorDetails.photo);
            var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            var currentdate = new Date();
            var currentDay = days[currentdate.getDay()];
            hour = currentdate.getHours();
            minute = currentdate.getMinutes();
            if (hour < 10) {
                hour = '0' + hour
            }
            if (minute < 10) {
                minute = '0' + minute
            }
            var currentHours = hour + ':' + minute;
            // $("#vendor_open_time").append(vendorDetails.opentime + ' - ' + vendorDetails.closetime);
            if (vendorDetails.hasOwnProperty('workingHours')) {
                for (i = 0; i < vendorDetails.workingHours.length; i++) {
                    if (vendorDetails.workingHours[i]['day'] == currentDay) {
                        if (vendorDetails.workingHours[i]['timeslot'].length != 0) {
                            for (j = 0; j < vendorDetails.workingHours[i]['timeslot'].length; j++) {
                                var timeslot = vendorDetails.workingHours[i]['timeslot'][j];
                                var TimeslotHourVar = {
                                    'from': timeslot[`from`],
                                    'to': timeslot[`to`],
                                    'closeingType': timeslot[`closeingType`]
                                };
                                var [h, m] = timeslot[`from`].split(":");
                                var from = ((h % 12 ? h % 12 : 12) + ":" + m, h >= 12 ? 'PM' : 'AM');

                                var [h2, m2] = timeslot[`to`].split(":");
                                var to = ((h2 % 12 ? h2 % 12 : 12) + ":" + m2, h2 >= 12 ? 'PM' : 'AM');

                                if (currentHours >= timeslot[`from`] && currentHours <= timeslot[`to`]) {
                                    $('.add-to-cart').show();
                                } else {
                                    $('.add-to-cart').hide();
                                }
                            }

                        } else {
                            $('.add-to-cart').hide();
                        }
                    }
                }
            } else {
                $('.add-to-cart').hide();
            }


            $("#vendor_id").val(vendorDetails.id);
            $(".vendor_name").val(vendorDetails.title);
            $("#vendor_location").val(vendorDetails.location);
            $("#vendor_latitude").val(vendorDetails.latitude);
            $("#vendor_longitude").val(vendorDetails.longitude);
            $("#vendor_image").val(vendorDetails.photo);

            var view_vendor_details = "{{ route('restaurant',':id')}}";
            view_vendor_details = view_vendor_details.replace(':id', 'id=' + vendorID);

            if (vendorDetails.photo != null && vendorDetails.photo != "") {
                photo = vendorDetails.photo;
            } else {
                photo = placeholderImageSrc;
            }
            if (vendorDetails.hasOwnProperty('reviewsCount') && vendorDetails.reviewsCount != '') {
                reviewsCount = vendorDetails.reviewsCount;
            } else {
                reviewsCount = 0;
            }

            $("#seller-image").html('<a href="' + view_vendor_details + '"><img style="height: 65px; width: 65px; border-radius: 50%" src="' + photo + '"></a>');
            $(".vendor_name").html('<a href="' + view_vendor_details + '">' + vendorDetails.title + '</a>');
            $(".store_url").attr("href", view_vendor_details);
            $(".vendor-total-review").text(reviewsCount);

            try {
                if (deliveryChargemain.vendor_can_modify) {
                    if (vendorDetails.deliveryCharge) {
                        if (vendorDetails.deliveryCharge.delivery_charges_per_km && vendorDetails.deliveryCharge.minimum_delivery_charges && vendorDetails.deliveryCharge.minimum_delivery_charges_within_km) {
                            deliveryChargemain = vendorDetails.deliveryCharge;
                        }
                    }
                }
            } catch (error) {
            }

            if (vendorDetails.hasOwnProperty('specialDiscount')) {
                specialOfferVendor = vendorDetails.specialDiscount;
            }

            var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            var currentdate = new Date();
            var currentDay = days[currentdate.getDay()];
            var currentTime = currentdate.getHours() + ":" + currentdate.getMinutes();

            if (enableSpecialOffer) {
                if (specialOfferVendor.length != 0) {
                    for (i = 0; i < specialOfferVendor.length; i++) {
                        if (specialOfferVendor[i]['day'] == currentDay) {
                            if (specialOfferVendor[i]['timeslot'].length > 0) {
                                for (j = 0; j < specialOfferVendor[i]['timeslot'].length; j++) {
                                    if (currentTime >= specialOfferVendor[i]['timeslot'][j]['from'] && currentTime <= specialOfferVendor[i]['timeslot'][j]['to']) {
                                        if (specialOfferVendor[i]['timeslot'][j]['discount_type'] == 'delivery') {
                                            specialOfferForHour = [];
                                            specialOfferForHour.push(specialOfferVendor[i]['timeslot'][j]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            setCookie('specialOfferForHourMain', JSON.stringify(specialOfferForHour), 365);
        })
    }

    async function getVendorProducts(vendorID) {

        var total_item = 0;

        await database.collection('vendor_categories').where("publish", "==", true).get().then(async function (catsanpshots) {
            var catdata = [];
            catsanpshots.docs.forEach((listval) => {
                var datas = listval.data();
                catdata.push(datas);
            });

            var categoryId = [];
            for (i = 0; i < catdata.length; i++) {

                categoryId.push(catdata[i].id);

                if (takeaway == 'true' || takeaway == true) {
                    await database.collection('vendor_products').where("vendorID", "==", vendorID).where("publish", "==", true).where('categoryID', '==', catdata[i].id).get().then(function (snapshots) {
                        total_item += snapshots.docs.length;
                    });

                } else {
                    await database.collection('vendor_products').where('takeawayOption', '==', false).where("vendorID", "==", vendorID).where("publish", "==", true).where('categoryID', '==', catdata[i].id).get().then(function (snapshots) {
                        total_item += snapshots.docs.length;
                    });

                }

            }
            $(".vendor-total-product").text(total_item);

        });


        if (takeaway == 'true' || takeaway == true) {
            database.collection('vendor_products').where("vendorID", "==", vendorID).where("publish", "==", true).where('id', "!=", id).limit(5).get().then(async function (snapshots) {
                var html = '';
                html = buildVendorProductsHTML(snapshots);
                if (html != '') {
                    var append_list = document.getElementById('vendor-products');
                    append_list.innerHTML = html;
                }
            });
        } else {
            database.collection('vendor_products').where("vendorID", "==", vendorID).where('takeawayOption', '==', false).where("publish", "==", true).where('id', "!=", id).limit(5).get().then(async function (snapshots) {
                var html = '';
                html = buildVendorProductsHTML(snapshots);
                if (html != '') {
                    var append_list = document.getElementById('vendor-products');
                    append_list.innerHTML = html;
                }
            });

        }

        return total_item;
    }

    function buildVendorProductsHTML(snapshots) {

        var html = '';

        var alldata = [];
        snapshots.docs.forEach((listval) => {
            var datas = listval.data();
            datas.id = listval.id;
            alldata.push(datas);
        });

        alldata.forEach((listval) => {

            var val = listval;
            var vendor_id_single = val.id;
            var view_vendor_details = "{{ route('productDetail',':id')}}";
            view_vendor_details = view_vendor_details.replace(':id', vendor_id_single);

            var rating = 0;
            var reviewsCount = 0;
            if (val.hasOwnProperty('reviewsSum') && val.reviewsSum != 0 && val.hasOwnProperty('reviewsCount') && val.reviewsCount != 0) {
                rating = (val.reviewsSum / val.reviewsCount);
                rating = Math.round(rating * 10) / 10;
                reviewsCount = val.reviewsCount;
            }

            if (val.photo) {
                photo = val.photo;
            } else {
                photo = placeholderImageSrc;
            }

            val.price = parseFloat(val.price);
            var dis_price = '';
            var or_price = '';
            if (val.hasOwnProperty('disPrice') && val.disPrice != '' && val.disPrice != '0') {
                val.disPrice = parseFloat(val.disPrice);
                if (currencyAtRight) {
                    or_price = val.price.toFixed(decimal_degits) + "" + currentCurrency;
                    dis_price = val.disPrice.toFixed(decimal_degits) + "" + currentCurrency;
                } else {
                    or_price = currentCurrency + "" + val.price.toFixed(decimal_degits);
                    dis_price = currentCurrency + "" + val.disPrice.toFixed(decimal_degits);
                }
            } else {
                var or_price = '';
                if (currencyAtRight) {
                    or_price = val.price.toFixed(decimal_degits) + "" + currentCurrency;
                } else {
                    or_price = currentCurrency + "" + val.price.toFixed(decimal_degits);
                }
            }

            html = html + '<div class="store-product" data-product-id="' + vendor_id_single + '">';
            html = html + '<div class="product-content">';
            html = html + '<div class="store-image">';
            html = html + '<img src="' + photo + '">';
            html = html + '</div>';
            html = html + '<div class="product-detail">';
            html = html + '<div class="basic">';
            html = html + '<div class="product-name">';
            html = html + '<span class="flash-product-title">' + val.name + '</span>';
            html = html + '</div>';
            html = html + '<div class="product-review">';
            html = html + '<span class="badge badge-success">' + rating + ' <i class="feather-star"></i></span>';
            html = html + '<label class="badge-style2">&nbsp;(' + reviewsCount + ')</label>';
            html = html + '</div>';

            html = html + '<div class="strike-price">';
            if (dis_price && or_price) {
                html = html + '<strike>' + or_price + '</strike>';
                html = html + '<div class="product-price">' + dis_price + '</div>';
            } else {
                html = html + '<div class="product-price">' + or_price + '</div>';
            }
            html = html + '</div>';
            html = html + '</div>';
            html = html + '</div>';
            html = html + '</div>';
            html = html + '</div>';
            html = html + '</div>';
        });

        return html;
    }

    function getUsersReviews(vendorProduct, limit) {

        var vendorRatings = database.collection('foods_review').where('productId', "==", vendorProduct.id);
        if (limit && review_pagesize) {
            var reviewHTML = '';
            vendorRatings.limit(review_pagesize).get().then(async function (snapshots) {
                review_start = snapshots.docs[snapshots.docs.length - 1];
                if (snapshots.docs.length > 3) {
                    $(".see_all_review_div").show();
                }
                if (snapshots.docs.length == 0) {
                    $(".no_review_fount").show();
                }
                reviewHTML = buildRatingsAndReviewsHTML(vendorProduct, snapshots);
                if (reviewHTML != '') {
                    jQuery("#customers_ratings_and_review").append(reviewHTML);
                }
            });
        } else if (review_start) {
            vendorRatings.startAfter(review_start).limit(review_pagesize).get().then(async function (snapshots) {
                review_start = snapshots.docs[snapshots.docs.length - 1];
                reviewHTML = buildRatingsAndReviewsHTML(vendorProduct, snapshots);
                if (reviewHTML != '') {
                    jQuery("#customers_ratings_and_review").append(reviewHTML);
                }
            });
        }

        if (vendorProduct.product_specification != null && vendorProduct.product_specification != "" && !$.isEmptyObject(vendorProduct.product_specification)) {
            var html = '';
            html = html + '<div class="specification mt-3">';
            var label_specification = "{{trans('lang.specification')}}";
            html += '<h3>' + label_specification + '</h3>';
            html = html + '<div class="row">';
            $.each(vendorProduct.product_specification, function (key, value) {
                html = html + '<div class="col-md-12 prospe-info-box pb-3 border-bottom mb-3">';
                html = html + '<div class="prospe-info-box-list d-flex align-items-center">';
                html = html + '<span>' + key + '</span>';
                html = html + '<label>' + value + '</label>';
                html = html + '</div>';
                html = html + '</div>';
            });
            html = html + '</div>';
            html = html + '</div>';
            jQuery(".main-specification").append(html);
        }
    }

    function buildRatingsAndReviewsHTML(vendorProduct, reviewsSnapshots) {

        var reviewhtml = '<h3>{{trans("lang.customer_reviews")}}</h3>';

        var rating = 0;
        var reviewsCount = 0;
        if (vendorProduct.hasOwnProperty('reviewsSum') && vendorProduct.reviewsSum != 0 && vendorProduct.hasOwnProperty('reviewsCount') && vendorProduct.reviewsCount != 0) {
            rating = (vendorProduct.reviewsSum / vendorProduct.reviewsCount);
            rating = Math.round(rating * 10) / 10;
            reviewsCount = vendorProduct.reviewsCount;
            reviewhtml = reviewhtml + '<div class="overall-rating mb-4">';
            reviewhtml = reviewhtml + '<span class="badge badge-success">' + rating + ' <i class="feather-star"></i></span>';
            if (reviewsCount == 1) {
                reviewhtml = reviewhtml + '<span class="count">' + reviewsCount + ' {{trans("lang.review")}}</span>';
            } else {
                reviewhtml = reviewhtml + '<span class="count">' + reviewsCount + ' {{trans("lang.reviews")}}</span>';
            }
            reviewhtml = reviewhtml + '</div>';
        }

        if (vendorProduct.hasOwnProperty('reviewAttributes') && vendorProduct.reviewAttributes != null) {
            reviewhtml += '<div class="attribute-ratings feature-rating mb-4">';
            var label_feature = "{{trans('lang.byfeature')}}";
            reviewhtml += '<h3 class="mb-2">' + label_feature + '</h3>';
            reviewhtml += '<div class="media-body">';
            $.each(vendorProduct.reviewAttributes, function (aid, data) {
                var rating = (data.reviewsSum / data.reviewsCount);
                rating = Math.round(rating * 10) / 10;
                var reviewsCount = data.reviewsCount;
                var at_id = aid;
                var at_title = reviewAttributes[aid];
                var at_value = rating;
                reviewhtml += '<div class="feature-reviews-members-header d-flex mb-3">';
                reviewhtml += '<h6 class="mb-0">' + at_title + '</h6>';
                reviewhtml = reviewhtml + '<div class="rating-info ml-auto d-flex">';
                reviewhtml = reviewhtml + '<div class="star-rating">';
                if (rating > 1) {
                    reviewhtml = reviewhtml + '<i class="feather-star text-warning"></i>';
                } else {
                    reviewhtml = reviewhtml + '<i class="feather-star"></i>';
                }
                if (rating > 2 || rating > 1.5) {
                    reviewhtml = reviewhtml + '<i class="feather-star text-warning"></i>';
                } else {
                    reviewhtml = reviewhtml + '<i class="feather-star"></i>';
                }
                if (rating > 3 || rating > 2.5) {
                    reviewhtml = reviewhtml + '<i class="feather-star text-warning"></i>';
                } else {
                    reviewhtml = reviewhtml + '<i class="feather-star"></i>';
                }
                if (rating > 4 || rating > 3.5) {
                    reviewhtml = reviewhtml + '<i class="feather-star text-warning"></i>';
                } else {
                    reviewhtml = reviewhtml + '<i class="feather-star"></i>';
                }
                if (rating > 5 || rating > 4.5) {
                    reviewhtml = reviewhtml + '<i class="feather-star text-warning"></i>';
                } else {
                    reviewhtml = reviewhtml + '<i class="feather-star"></i>';
                }
                reviewhtml += '</div>';

                reviewhtml += '<div class="count-rating ml-2">';
                reviewhtml += '<span class="count">' + rating + '</span>';
                reviewhtml += '</div>';

                reviewhtml += '</div>';

                reviewhtml += '</div>';
            });
            reviewhtml += '</div>';

            reviewhtml += '</div>';
        }

        var allreviewdata = [];
        reviewsSnapshots.docs.forEach((listval) => {
            var reviewDatas = listval.data();
            reviewDatas.id = listval.id;
            allreviewdata.push(reviewDatas);
        });

        reviewhtml += '<div class="user-ratings">';
        allreviewdata.forEach((listval) => {
            var val = listval;
            var rating = val.rating;
            reviewhtml = reviewhtml + '<div class="reviews-members py-3 border mb-3"><div class="media">';
            if (val.profile == '' || val.profile.indexOf('firebasestorage.googleapis.com') == -1) {
                reviewhtml = reviewhtml + '<a href="javascript:void(0);"><img alt="#" src="' + placeholderImageSrc + '" class="mr-3 rounded-pill"></a>';
            } else {
                try {
                    reviewhtml = reviewhtml + '<a href="javascript:void(0);"><img alt="#" src="' + val.profile + '" class="mr-3 rounded-pill"></a>';
                } catch (err) {
                    reviewhtml = reviewhtml + '<a href="javascript:void(0);"><img alt="#" src="' + placeholderImageSrc + '" class="mr-3 rounded-pill"></a>';
                }
            }
            reviewhtml = reviewhtml + '<div class="media-body d-flex"><div class="reviews-members-header"><h6 class="mb-0"><a class="text-dark" href="javascript:void(0);">' + val.uname + '</a></h6><div class="star-rating"><div class="d-inline-block" style="font-size: 14px;">';
            if (rating > 1) {
                reviewhtml = reviewhtml + '<i class="feather-star text-warning"></i>';
            } else {
                reviewhtml = reviewhtml + '<i class="feather-star"></i>';
            }
            if (rating > 2 || rating > 1.5) {
                reviewhtml = reviewhtml + '<i class="feather-star text-warning"></i>';
            } else {
                reviewhtml = reviewhtml + '<i class="feather-star"></i>';
            }
            if (rating > 3 || rating > 2.5) {
                reviewhtml = reviewhtml + '<i class="feather-star text-warning"></i>';
            } else {
                reviewhtml = reviewhtml + '<i class="feather-star"></i>';
            }
            if (rating > 4 || rating > 3.5) {
                reviewhtml = reviewhtml + '<i class="feather-star text-warning"></i>';
            } else {
                reviewhtml = reviewhtml + '<i class="feather-star"></i>';
            }
            if (rating > 5 || rating > 4.5) {
                reviewhtml = reviewhtml + '<i class="feather-star text-warning"></i>';
            } else {
                reviewhtml = reviewhtml + '<i class="feather-star"></i>';
            }
            reviewhtml = reviewhtml + '</div></div>';
            reviewhtml = reviewhtml + '</div>';

            reviewhtml = reviewhtml + '<div class="review-date ml-auto">';
            if (val.createdAt != null && val.createdAt != "") {
                var review_date = val.createdAt.toDate().toLocaleDateString('en', {
                    year: "numeric",
                    month: "short",
                    day: "numeric"
                });
                reviewhtml = reviewhtml + '<span>' + review_date + '</span>';
            }
            reviewhtml = reviewhtml + '</div>';

            var photos = '';
            if (val.photos.length > 0) {
                photos += '<div class="photos"><ul>';
                $.each(val.photos, function (key, img) {
                    photos += '<li><img src="' + img + '" width="100"></li>';
                });
                photos += '</ul></div>';
            }

            reviewhtml = reviewhtml + '</div></div><div class="reviews-members-body w-100"><p class="mb-2">' + val.comment + '</p>' + photos + '</div></div>';
        });
        reviewhtml += '</div>';

        return reviewhtml;
    }

    /* Add to favorite code starts */
    function checkFavoriteProduct(productID) {
        if (user_uuid != undefined) {
            var user_id = user_uuid;
        } else {
            var user_id = '';
        }
        database.collection('favorite_item').where('product_id', '==', productID).where('user_id', '==', user_id).get().then(async function (favoriteItemsnapshots) {

            if (favoriteItemsnapshots.docs.length > 0) {
                $('.addToFavorite').html('<i class="font-weight-bold fa fa-heart" style="color:red"></i>');
            } else {
                $('.addToFavorite').html('<i class="font-weight-bold feather-heart" ></i>');
            }
        });
    }


    $(document).on("click", "a[name='loginAlert']", function (e) {
        alert('Please Login For Add to favorite');
    });
    $(document).on("click", "a[name='addToFavorite']", function (e) {

        var user_id = user_uuid;
        var store_id = this.id;
        var product_id = '<?php echo $id; ?>';

        database.collection('favorite_item').where('product_id', '==', product_id).where('user_id', '==', user_id).get().then(async function (favoriteItemsnapshots) {
            if (favoriteItemsnapshots.docs.length > 0) {
                var id = favoriteItemsnapshots.docs[0].id;
                database.collection('favorite_item').doc(id).delete().then(function () {
                    $('.addToFavorite').html('<i class="font-weight-bold feather-heart" ></i>');
                });
            } else {
                var id = "<?php echo uniqid();?>";
                database.collection('favorite_item').doc(id).set({
                    'store_id': store_id,
                    'user_id': user_id,
                    'product_id': product_id
                }).then(function (result) {
                    $('.addToFavorite').html('<i class="font-weight-bold fa fa-heart" style="color:red"></i>');
                });
            }
        });


    });

    /* Add to favorite code Ends */
    async function buildHTML(snapshots) {

        var vendorProduct = snapshots.data();
        if(vendorProduct != undefined ){
        var vendorID = vendorProduct.vendorID;
        var productID = vendorProduct.id;
        checkFavoriteProduct(productID);
        await getVendorProducts(vendorID);

        getUsersReviews(vendorProduct, true);

        getRelatedProducts(vendorProduct);

        if (vendorProduct.brandID) {
            getBrandData(vendorProduct.brandID);
        }

        var html = '';

        var price = vendorProduct.price;
        if (vendorProduct.hasOwnProperty('disPrice') && vendorProduct.disPrice != '0') {
            price = vendorProduct.disPrice;
        }

        if (vendorProduct.photo != null && vendorProduct.photo != "") {
            photo = vendorProduct.photo;
        } else {
            photo = placeholderImageSrc;
        }

        var view_product_details = "{{ route('productDetail',':id')}}";
        view_product_details = view_product_details.replace(':id', 'id=' + vendorProduct.id);

        /*---start row---*/
        html = html + '<div class="col-md-6 rent-cardet-left">';
        if (vendorProduct.photos != null && vendorProduct.photos.length > 0) {
            html = html + '<div class="main-slider">';
            vendorProduct.photos.forEach((photo) => {
                html = html + '<div class="product-image">';
                html = html + '<img alt="#" src="' + photo + '" class="img-fluid item-img w-100">';
                html = html + '</div>';
            });
            html = html + '</div>';
            html = html + '<div class="nav-slider">';
            vendorProduct.photos.forEach((photo) => {
                html = html + '<div class="product-image">';
                html = html + '<img alt="#" src="' + photo + '" class="img-fluid item-img w-100">';
                html = html + '</div>';
            });
            html = html + '</div>';
        } else {
            html = html + '<div class="product-image">';
            html = html + '<img alt="#" src="' + photo + '" class="img-fluid item-img w-100">';
            html = html + '</div>';
        }
        html = html + '</div>';

        html = html + '<div class="col-md-6 rent-cardet-right">';

        html = html + '<div class="carrent-det-rg-inner">';

        html = html + '<div class="car-det-head mb-3">';

        html = html + '<div class="d-flex">';

        html = html + '<div class="car-det-title">';

        html = html + '<h2>' + vendorProduct.name + '</h2>';
        var rating = 0;
        var reviewsCount = 0;
        if (vendorProduct.hasOwnProperty('reviewsSum') && vendorProduct.reviewsSum != 0 && vendorProduct.hasOwnProperty('reviewsCount') && vendorProduct.reviewsCount != 0) {
            rating = (vendorProduct.reviewsSum / vendorProduct.reviewsCount);
            rating = Math.round(rating * 10) / 10;
            reviewsCount = vendorProduct.reviewsCount;
        }
        html = html + '<div class="rating star position-relative mt-2">';
        html = html + '<span class="badge badge-success">' + rating + ' <i class="feather-star"></i></span>';
        if (reviewsCount == 1) {
            html = html + '<span class="count">' + reviewsCount + ' {{trans("lang.review")}}</span>';
        } else {
            html = html + '<span class="count">' + reviewsCount + ' {{trans("lang.reviews")}}</span>';
        }
        <?php if(Auth::check()){?>
            html = html + '<a  name="addToFavorite" id="' + vendorID + '" class="count addToFavorite" href="javascript:void(0)"><i  class="font-weight-bold feather-heart"></i></a>';
        <?php }else{?>
            html = html + '<a  name="loginAlert" class="loginAlert count" href="javascript:void(0)"><i  class="font-weight-bold feather-heart"></i></a>';
        <?php }?>

            html = html + '</div>';

        html = html + '</div>';

        /*html = html+ '<div class="ratings">
            html = html+ '<ul class="rating" data-rating="0">';
                html = html+ '<li class="rating__item"></li>';
            html = html+ '</ul>';
            html = html+ '<span>0</span>';
        html = html+ '</div>';*/

        html = html + '<div class="car-det-price ml-auto">';
        if (vendorProduct.item_attribute != null && vendorProduct.item_attribute != "" && vendorProduct.item_attribute.attributes.length > 0 && vendorProduct.item_attribute.variants.length > 0) {

            html = html + '<span class="price">';
            html = html + '<div class="variation_info" id="variation_info_' + vendorProduct.id + '">';
            html = html + '<span id="variant_price"></span>';
            html = html + '<span id="variant_qty"></span>';
            html = html + '</div>';
            html = html + '</span>';

        } else {

            vendorProduct.price = parseFloat(vendorProduct.price);
            if (vendorProduct.hasOwnProperty('disPrice') && vendorProduct.disPrice != '' && vendorProduct.disPrice != '0') {

                vendorProduct.disPrice = parseFloat(vendorProduct.disPrice);
                var dis_price = '';
                var or_price = '';
                if (currencyAtRight) {
                    or_price = vendorProduct.price.toFixed(decimal_degits) + "" + currentCurrency;
                    dis_price = vendorProduct.disPrice.toFixed(decimal_degits) + "" + currentCurrency;
                } else {
                    or_price = currentCurrency + "" + vendorProduct.price.toFixed(decimal_degits);
                    dis_price = currentCurrency + "" + vendorProduct.disPrice.toFixed(decimal_degits);
                }
                html = html + '<span class="price">' + dis_price + '  <s>' + or_price + '</s></span>';
            } else {
                var or_price = '';
                if (currencyAtRight) {
                    or_price = vendorProduct.price.toFixed(decimal_degits) + "" + currentCurrency;
                } else {
                    or_price = currentCurrency + "" + vendorProduct.price.toFixed(decimal_degits);
                }
                html = html + '<span class="price">' + or_price + '</span>';
            }

            if (vendorProduct.hasOwnProperty('quantity')) {
                if (vendorProduct.quantity == -1) {
                    html = html + '<span id="variant_qty">{{trans("lang.qty_left")}}: {{trans("lang.unlimited")}}</span>';
                } else {
                    html = html + '<span id="variant_qty">{{trans("lang.qty_left")}}: ' + vendorProduct.quantity + '</span>';
                }
            }
        }


        html = html + '</div>';

        html = html + '</div>';
        html = html + '</div>';

        if (vendorProduct.brandID) {
            html = html + '<div class="brand mt-2 mb-3">';
            html = html + '<h3>{{trans("lang.brand")}} <span class="brand_name"></span></h3>';

            html = html + '</div>';
            database.collection('brands').doc(vendorProduct.brandID).get().then((result) => {
                var brand_name = result.exists ? result.data().title : '';
                if (brand_name) {
                    var view_brand_products = "";
                    view_brand_products = view_brand_products.replace(':type', 'brand');
                    view_brand_products = view_brand_products.replace(':id', vendorProduct.brandID);
                    $(".brand_name").html(' | <a href="' + view_brand_products + '">' + brand_name + '</a>');
                }
            });
        }

        console.log(vendorProduct);

        html = html + '<div class="store mt-2 mb-3">';
        html = html + '<h3>{{trans("lang.store")}} | <span class="vendor_name"></span></h3>';
        html = html + '</div>';

        html = html + '<div class="description mt-2 mb-3">';
        html = html + '<p>' + vendorProduct.description + '</p>';
        html = html + '</div>';

        if (vendorProduct.item_attribute != null && vendorProduct.item_attribute != "" && vendorProduct.item_attribute.attributes.length > 0 && vendorProduct.item_attribute.variants.length > 0) {
            var attributes = vendorProduct.item_attribute.attributes;
            var variants = vendorProduct.item_attribute.variants;
            vendorProduct.product_specification = encodeURIComponent(vendorProduct.product_specification);
            html = html + '<div class="attributes mt-2 mb-0">';
            html = html + '<div class="v-boxariants">';
            html = html + '<div class="attribute_list" id="attribute-list-' + vendorProduct.id + '" data-pid="' + vendorProduct.id + '" data-product="' + btoa(JSON.stringify(vendorProduct)) + '"></div>';
            html = html + '</div>';
            html = html + '</div>';
            getVariantsHtml(vendorProduct, attributes, variants)
        }

        if (vendorProduct.hasOwnProperty('addOnsPrice') && vendorProduct.addOnsPrice.length > 0) {
            html = html + '<div class="addons mt-2 mb-3">';
            html += '<h3 class="font-weight-bold">{{trans('lang.addons')}}</h3>';
            var total = 0;
            vendorProduct.addOnsPrice.forEach(async (product_price) => {
                html += '<div class="addons-option">';
                html += '<div class="custom-control custom-checkbox border-bottom py-2">';
                html += '<input data-price="' + product_price + '" type="checkbox" id="' + vendorProduct.id + '_extra_' + total + '" name="extra_' + vendorProduct.id + '" value="' + vendorProduct.addOnsTitle[total] + '" class="custom-control-input extra_' + vendorProduct.id + ' addon-checkbox">';
                html += '<label class="custom-control-label" for="' + vendorProduct.id + '_extra_' + total + '">' + vendorProduct.addOnsTitle[total] + ' <span class="">';
                if (currencyAtRight) {
                    product_price = parseFloat(product_price).toFixed(decimal_degits) + "" + currentCurrency;
                } else {
                    product_price = currentCurrency + "" + parseFloat(product_price).toFixed(decimal_degits);

                }
                html += '+' +product_price + '</span></label>';
                html += '</div>';
                html += '</div>';
                total++;
            });
            html = html + '</div>';
        }

        html = html + '<div class="quantity mt-2 mb-3">';
        html += '<div class="d-flex align-items-center product-item-box">';
        var label_qty = "{{trans('lang.quantity')}}";
        html += '<h3 class="m-0">' + label_qty + '</h3>';
        html += '<div class="ml-auto">';
        html += '<span class="count-number">';
        html += '<button type="button" class="btn-sm left dec btn btn-outline-secondary food_count_decrese"><i class="feather-minus"></i></button>';
        html += '<input class="count-number-input" name="quantity_' + vendorProduct.id + '" type="text"  value="1">';
        html += '<button type="button" class="btn-sm right inc btn btn-outline-secondary"><i class="feather-plus"></i></button>';
        html += '</span>';
        html += '</div>';
        html += '</div>';
        html = html + '</div>';

        html = html + '<div class="addtocart mt-2 mb-3">';
        html += "<button data-id='" + String(vendorProduct.id) + "' type='button' class='add-to-cart btn btn-primary btn-lg btn-block' >{{trans('lang.add_to_cart')}}</button>";
        html += '<input type="hidden" name="name_' + vendorProduct.id + '" id="name_' + vendorProduct.id + '" value="' + vendorProduct.name + '">';
        html += '<input type="hidden" id="price_' + vendorProduct.id + '" name="price_' + vendorProduct.id + '" value="' + price + '">';
        html += '<input type="hidden" id="dis_price_' + vendorProduct.id + '" name="dis_price_' + vendorProduct.id + '" value="' + vendorProduct.disPrice + '">';
        html += '<input type="hidden" id="quantity_' + vendorProduct.id + '" name="quantity_' + vendorProduct.id + '" value="' + vendorProduct.quantity + '">';
        html += '<input type="hidden" id="image_' + vendorProduct.id + '" name="image_' + vendorProduct.id + '" value="' + vendorProduct.photo + '">';
        html += '<input type="hidden" id="veg_' + vendorProduct.id + '" name="veg_' + vendorProduct.id + '" value="' + vendorProduct.veg + '">';
        html += '<input type="hidden" id="category_id_' + vendorProduct.id + '" name="category_id_' + vendorProduct.id + '" value="' + vendorProduct.categoryID + '">';

        html += "<button data-id='" + String(vendorProduct.id) + "' type='button' class='add-to-cart btn btn-primary btn-lg btn-block booknow' >{{trans('lang.book_now')}}</button>";
        html = html + '<div class="description mt-2 mb-3">';
        getVendorDetails(vendorID);


        html = html + '</div>';
        html = html + '</div>';

        return html;

    }
}
    function getBrandData(brandID) {
        database.collection('brands').doc(brandID).get().then((result) => {
            return result.exists ? result.data() : null;
        });
    }

    async function getRelatedProducts(vendorProduct) {
        var html = '';
        if (takeaway == 'true' || takeaway == true) {
            database.collection('vendor_products').where('categoryID', "==", vendorProduct.categoryID).where("publish", "==", true).where('id', "!=", vendorProduct.id).limit(4).get().then(async function (snapshots) {
                html = buildHTMLRelatedProducts(snapshots);
                if (html != '') {
                    var append_list = document.getElementById('related_products');
                    append_list.innerHTML = html;
                }
            });
        } else {
            database.collection('vendor_products').where('categoryID', "==", vendorProduct.categoryID).where("publish", "==", true).where('takeawayOption', '==', false).where('id', "!=", vendorProduct.id).limit(4).get().then(async function (snapshots) {
                html = buildHTMLRelatedProducts(snapshots);
                if (html != '') {
                    var append_list = document.getElementById('related_products');
                    append_list.innerHTML = html;
                }
            });

        }
    }

    function buildHTMLRelatedProducts(snapshots) {

        var html = '';

        var alldata = [];
        snapshots.docs.forEach((listval) => {
            var datas = listval.data();
            datas.id = listval.id;
            alldata.push(datas);
        });
        var count = 0;
        var popularFoodCount = 0;

        html = html + '<h3>{{trans("lang.related_products")}}</h3>';

        html = html + '<div class="row">';

        alldata.forEach((listval) => {

            var val = listval;
            var vendor_id_single = val.id;
            var view_vendor_details = "{{ route('productDetail',':id')}}";
            view_vendor_details = view_vendor_details.replace(':id', vendor_id_single);

            var rating = 0;
            var reviewsCount = 0;
            if (val.hasOwnProperty('reviewsSum') && val.reviewsSum != 0 && val.hasOwnProperty('reviewsCount') && val.reviewsCount != 0) {
                rating = (val.reviewsSum / val.reviewsCount);
                rating = Math.round(rating * 10) / 10;
                reviewsCount = val.reviewsCount;
            }

            html = html + '<div class="col-md-3 product-list"><div class="list-card position-relative"><div class="list-card-image">';

            if (val.photo) {
                photo = val.photo;
            } else {
                photo = placeholderImageSrc;
            }

            html = html + '<a href="' + view_vendor_details + '"><img alt="#" src="' + photo + '" class="img-fluid item-img w-100"></a></div><div class="py-2 position-relative"><div class="list-card-body position-relative"><h6 class="product-title mb-1"><a href="' + view_vendor_details + '" class="text-black">' + val.name + '</a></h6>';
            /*var popularItemCategorytitle = popularItemCategory(val.categoryID, val.id);*/
            html = html + '<h6 class="mb-1 popular_food_category_ pro-cat" id="popular_food_category_' + val.categoryID + '_' + val.id + '" ></h6>';

            val.price = parseFloat(val.price);
            if (val.hasOwnProperty('disPrice') && val.disPrice != '' && val.disPrice != '0') {
                var dis_price = '';
                var or_price = '';
                val.disPrice = parseFloat(val.disPrice);
                if (currencyAtRight) {
                    or_price = val.price.toFixed(decimal_degits) + "" + currentCurrency;
                    dis_price = val.disPrice.toFixed(decimal_degits) + "" + currentCurrency;
                } else {
                    or_price = currentCurrency + "" + val.price.toFixed(decimal_degits);
                    dis_price = currentCurrency + "" + val.disPrice.toFixed(decimal_degits);
                }

                html = html + '<span class="pro-price">' + dis_price + '  <s>' + or_price + '</s></span>';
            } else {
                var or_price = '';
                if (currencyAtRight) {
                    or_price = val.price.toFixed(decimal_degits) + "" + currentCurrency;
                } else {
                    or_price = currentCurrency + "" + val.price.toFixed(decimal_degits);
                }

                html = html + '<span class="pro-price">' + or_price + '</span>'
            }

            html = html + '<div class="star position-relative mt-3"><span class="badge badge-success"><i class="feather-star"></i>' + rating + ' (' + reviewsCount + ')</span></div>';

            html = html + '</div>';

            html = html + '</div></div></div>';
        });

        html = html + '</div>';

        return html;
    }

    function slickCarousel() {
        $('.main-slider').slick({
            slidesToShow: 1,
            arrows: false,
            draggable: false
        });
        $('.nav-slider').slick({
            slidesToShow: 7,
            arrows: true
        });
    }

    async function getVariantsHtml(vendorProduct, attributes, variants) {
        var attributesHtml = '';
        for (attribute of attributes) {
            var attributeHtmlRes = getAttributeHtml(vendorProduct, attribute);
            var attributeHtml = await attributeHtmlRes.then(function (html) {
                return html;
            })
            attributesHtml += attributeHtml;
        }
        $('#attribute-list-' + vendorProduct.id).html(attributesHtml);

        var variation_info = {};
        var variation_sku = '';
        $('#attribute-list-' + vendorProduct.id + ' .attribute-drp').each(function () {
            variant_title = $(this).data('atitle') + '-';
            variation_sku += $(this).find('input[type="radio"]:checked').val() + '-';
            variation_info[$(this).data('atitle')] = $(this).find('input[type="radio"]:checked').val();
        });
        variation_sku = variation_sku.replace(/-$/, "");

        if (variation_sku) {
            var variant_info = $.map(vendorProduct.item_attribute.variants, function (v, i) {
                if (v.variant_sku == variation_sku) {
                    return v;
                }
            });
            var variant_id = variant_image = variant_price = variant_price = variant_quantity = '';
            if (variant_info.length > 0) {
                var variant_id = variant_info[0].variant_id;
                var variant_image = variant_info[0].variant_image;
                var variant_price = parseFloat(variant_info[0].variant_price);
                var variant_sku = variant_info[0].variant_sku;
                var variant_img = variant_info[0].variant_image;
                var variant_quantity = variant_info[0].variant_quantity;
                if (currencyAtRight) {
                    var pro_price = variant_price.toFixed(decimal_degits) + "" + currentCurrency;
                } else {
                    var pro_price = currentCurrency + "" + variant_price.toFixed(decimal_degits);
                }
                $('#variation_info_' + vendorProduct.id).find('#variant_price').html(pro_price);
                $('#variation_info_' + vendorProduct.id).find('#variant_price').attr('data-vid', variant_id);
                $('#variation_info_' + vendorProduct.id).find('#variant_price').attr('data-vprice', variant_price);
                $('#variation_info_' + vendorProduct.id).find('#variant_price').attr('data-vqty', variant_quantity);
                $('#variation_info_' + vendorProduct.id).find('#variant_price').attr('data-vsku', variant_sku);
                $('#variation_info_' + vendorProduct.id).find('#variant_price').attr('data-vimg', variant_img);
                $('#variation_info_' + vendorProduct.id).find('#variant_price').attr('data-vinfo', JSON.stringify(variation_info));
                if (variant_quantity == '-1') {
                    $('#variation_info_' + vendorProduct.id).find('#variant_qty').html('{{trans("lang.qty_left")}}: {{trans("lang.unlimited")}}');
                } else {
                    $('#variation_info_' + vendorProduct.id).find('#variant_qty').html('{{trans("lang.qty_left")}}: ' + variant_quantity);
                }
            }
        }
    }

    function getAttributeHtml(vendorProduct, attribute) {
        var html = '';
        var vendorAttributesRef = database.collection('vendor_attributes').where('id', "==", attribute.attribute_id);
        attributeHtmlRes = vendorAttributesRef.get().then(async function (attributeRef) {
            var attributeInfo = attributeRef.docs[0].data();
            html += '<div class="attribute-drp" data-aid="' + attribute.attribute_id + '" data-atitle="' + attributeInfo.title + '">';
            html += '<h3 class="attribute-label">' + attributeInfo.title + '</h3>';
            html += '<div class="attribute-options">';
            $.each(attribute.attribute_options, function (i, option) {
                var ischecked = (i == 0) ? 'checked="checked"' : '';
                html += '<div class="custom-control custom-radio border-bottom py-2 attribute-selection">';
                html += '<input type="radio" id="attribute-' + attribute.attribute_id + '-' + option + '" name="attribute-options-' + attribute.attribute_id + '" value="' + option + '" ' + ischecked + ' class="custom-control-input">';
                html += '<label class="custom-control-label" for="attribute-' + attribute.attribute_id + '-' + option + '">' + option + '</label>';
                html += '</div>';
            });
            html += '</div>';
            html += '</div>';
            return html;
        })
        return attributeHtmlRes;
    }

    function getVariantPrice(vendorProduct) {
        var vendorProduct = $.parseJSON(atob(vendorProduct));
        var variation_info = {};
        var variation_sku = '';
        $('#attribute-list-' + vendorProduct.id + ' .attribute-drp').each(function () {
            var aid = $(this).parent().parent().data('aid');
            variation_sku += $(this).find('input[type="radio"]:checked').val() + '-';
            variation_info[$(this).data('atitle')] = $(this).find('input[type="radio"]:checked').val();
        });
        variation_sku = variation_sku.replace(/-$/, "");
        if (variation_sku) {
            var variant_info = $.map(vendorProduct.item_attribute.variants, function (v, i) {
                if (v.variant_sku == variation_sku) {
                    return v;
                }
            });

            var variant_id = variant_image = variant_price = variant_price = variant_quantity = '';
            if (variant_info.length > 0) {

                var variant_id = variant_info[0].variant_id;
                var variant_image = variant_info[0].variant_image;
                if (variant_image == undefined) {
                    variant_image = placeholderImageSrc
                }
                var variant_price = parseFloat(variant_info[0].variant_price);
                var variant_sku = variant_info[0].variant_sku;
                var variant_quantity = variant_info[0].variant_quantity;
                if (currencyAtRight) {
                    var pro_price = variant_price.toFixed(decimal_degits) + "" + currentCurrency;
                } else {
                    var pro_price = currentCurrency + "" + variant_price.toFixed(decimal_degits);
                }
                $('#variation_info_' + vendorProduct.id).find('#variant_price').html(pro_price);
                $('#variation_info_' + vendorProduct.id).find('#variant_price').attr('data-vid', variant_id);
                $('#variation_info_' + vendorProduct.id).find('#variant_price').attr('data-vprice', variant_price);
                $('#variation_info_' + vendorProduct.id).find('#variant_price').attr('data-vqty', variant_quantity);
                $('#variation_info_' + vendorProduct.id).find('#variant_price').attr('data-vsku', variant_sku);
                $('#variation_info_' + vendorProduct.id).find('#variant_price').attr('data-vimg', variant_image);

                $('#variation_info_' + vendorProduct.id).find('#variant_price').attr('data-vinfo', JSON.stringify(variation_info));
                if (variant_quantity == '-1') {
                    $('#variation_info_' + vendorProduct.id).find('#variant_qty').html('{{trans("lang.qty_left")}}: {{trans("lang.unlimited")}}');
                } else {
                    $('#variation_info_' + vendorProduct.id).find('#variant_qty').html('{{trans("lang.qty_left")}}: ' + variant_quantity);
                }
            }
        }
    }

</script>

@include('layouts.nav')