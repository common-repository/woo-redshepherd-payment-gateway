jQuery(function ($) {
	var checkout_form = $('form.woocommerce-checkout');
	checkout_form.submit(function (event) {
		// do something
		$('#rd_chname').val(btoa($('#rdm_chname').val()));
		$('#rdm_chname').val('');
		$('#rd_ccNo').val(btoa($('#rdm_ccNo').val().split(" ").join("")));
		$('#rdm_ccNo').val('');
		$('#rd_expdate').val(btoa($('#rdm_expdate').val().split(" ").join("").split("/").join("20")));
		$('#rdm_expdate').val('');
		$('#rd_cvv').val(btoa($('#rdm_cvv').val()));
		$('#rdm_cvv').val('');

		$('#rd_ahname').val(btoa($('#rdm_ahname').val()));
		$('#rdm_ahname').val('');
		$('#rd_baNo').val(btoa($('#rdm_baNo').val()));
		$('#rdm_baNo').val('');
		$('#rd_routingNo').val(btoa($('#rdm_routingNo').val()));
		$('#rdm_routingNo').val('');
		$('#rd_accountType').val(btoa($('#rdm_accountType').val()));
	});
});