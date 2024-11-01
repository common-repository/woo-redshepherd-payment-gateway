jQuery(function ($) {
	showForm();
	var card;
	$(document.body).on('updated_checkout', function () {

		showForm();
		initCard();
		$('#rdm_payment_type').change(function () {
			showForm();
		});
	});
	function showForm() {
		var selected = $('#rdm_payment_type').val();
		if (selected === 'card') {
			$('.card-form').show();
			$('.ach-form').hide();
		} else if (selected === 'ach') {
			$('.card-form').hide();
			$('.ach-form').show();
		}
	}

	function initCard() {
		// 	 card = $('form[name="checkout"]').card({
		card = $('#wc-redshepherd-cc-form').card({
			// a selector or DOM element for the container
			// where you want the card to appear
			container: '.card-wrapper', // *required*

			// all of the other options from above


			formatting: true, // optional - default true

			// Strings for translation - optional
			messages: {
				validDate: 'valid\ndate', // optional - default 'valid\nthru'
				monthYear: 'mm/yy', // optional - default 'month/year'
			},

			// Default placeholders for rendered fields - optional
			placeholders: {
				number: '•••• •••• •••• ••••',
				name: 'Full Name',
				expiry: '••/••',
				cvc: '•••'
			},

			formSelectors: {
				numberInput: 'input[name="rdm_ccNo"]', // optional — default input[name="number"]
				expiryInput: 'input[name="rdm_expdate"]', // optional — default input[name="expiry"]
				cvcInput: 'input[name="rdm_cvv"]', // optional — default input[name="cvc"]
				nameInput: 'input[name="rdm_chname"]' // optional - defaults input[name="name"]
			},

			// if true, will log helpful messages for setting up Card
			debug: true // optional - default false
		});
	}
});