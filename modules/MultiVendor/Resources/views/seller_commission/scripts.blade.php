@push('scripts')
    <script type="text/javascript">

     (function($){
         "use strict";
         var baseUrl = $('#app_base_url').val();
        $(document).ready(function () {
            $(document).on("click", ".edit_item", function () {
                let id = $(this).data("value");
                $('#pre-loader').removeClass('d-none');
                $.ajax({
                    url: baseUrl + "/admin/seller-commisions/" + id + "/edit",
                    type: "GET",
                    success: function (response) {
                        $('#pre-loader').addClass('d-none');
                        $(".edit_id").val(response.id);
                        $(".name").val(response.name);
                        if (response.id != 1) {
                            $('.rate_div').addClass('d-none');
                        }else {
                            $('.rate_div').removeClass('d-none');
                        }
                        $(".rate").val(response.rate);
                        $(".description").val(response.description);
                        if(response.status == 0){
                            $("#status_active").prop("checked", false);
                            $("#status_inactive").prop("checked", true);
                        }else{

                            $("#status_active").prop("checked", true);
                            $("#status_inactive").prop("checked", false);
                        }
                    },
                    error: function (error) {
                        toastr.error("{{__('common.error_message')}}","{{__('common.error')}}");
                        $('#pre-loader').addClass('d-none');
                    }
                });
            });
            $(document).on("submit", "#itemEditForm", function (event) {
                event.preventDefault();
                $('#pre-loader').removeClass('d-none');
                let id = $(".edit_id").val();
                let formData = $(this).serializeArray();
                $.ajax({
                    url: baseUrl + "/admin/seller-commisions/" + id + "/update",
                    data: formData,
                    headers: {'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')},
                    type: "POST",
                    dataType: "JSON",
                    success: function (response) {
                        $("#itemEditForm").trigger("reset");
                        toastr.success("{{__('common.updated_successfully')}}","{{__('common.success')}}")
                        itemList();
                        $('#pre-loader').addClass('d-none');
                        $('#edit_name_error').text('');
                        $('#edit_rate_error').text('');
                    },
                    error: function (response) {
                        if(response.responseJSON.error){
                            toastr.error(response.responseJSON.error ,"{{__('common.error')}}");
                            $('#pre-loader').addClass('d-none');
                            return false;
                        }
                        if (response) {
                            $.each(response.responseJSON.errors, function (key, message) {
                                $("#edit_" + key + "_error").html(message[0]);
                            });
                        }
                        $('#pre-loader').addClass('d-none');
                    }
                });
            });

            function itemList() {
                $.ajax({
                    url: "{{route("admin.seller_commission_item_index")}}",
                    type: "GET",
                    dataType: "HTML",
                    success: function (response) {
                        $("#item_list").html(response);
                        CRMTableThreeReactive();
                    },
                    error: function (error) {
                        console.log(error);
                    }
                });
            }

        });
     })(jQuery);

    </script>
@endpush
