<?php
/*
Plugin Name: WooCommerce RedShepherd Payment Gateway 
Plugin URI: https://www.redshepherd.com/woocommerce.html
Description: Red Shepherd Payment Gateway for WooCommerce
Author: Red Shepherd Inc.
Author URI: https://www.redshepherd.com
Version: 1.1
Copyright: © 2019 Red Shepherd Inc (email: support@redshepherd.com)
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/* This action hook registers the PHP class as a WooCommerce payment gateway */
add_filter( 'woocommerce_payment_gateways', 'redshepherd_add_gateway_class' );

function redshepherd_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_RedShepherd_Gateway'; // your class name is here
	return $gateways;
}
 
/* The plugin class, please note that it is inside plugins_loaded action hook */
add_action( 'plugins_loaded', 'redshepherd_init_gateway_class' );

function redshepherd_init_gateway_class() {
 
	class WC_RedShepherd_Gateway extends WC_Payment_Gateway {
    
    /* Constructor */
 		public function __construct() {
 
      $this->id = 'redshepherd'; // payment gateway plugin id
      $this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
      $this->has_fields = true; // in case you need a custom credit card form
      $this->method_title = 'Red Shepherd Gateway';
      $this->method_description = 'Red Shepherd Gateway Settings'; // will be displayed on the options page
    
      $this->supports = array(
        'products'
      );
    
      // Method with all the options fields
      $this->init_form_fields();
    
      // Load the settings.
      $this->init_settings();
      $this->title = $this->get_option( 'title' );
      $this->description = $this->get_option( 'description' );
      $this->enabled = $this->get_option( 'enabled' );
      $this->testmode = 'yes' === $this->get_option( 'testmode' );
      $this->publishable_key = $this->testmode ? $this->get_option( 'test_publishable_key' ) : $this->get_option( 'publishable_key' );
      $this->app_name = $this->testmode ? $this->get_option( 'test_app_name' ) : $this->get_option( 'app_name' );
      $this->gateway_endpoint = $this->testmode ? $this->get_option( 'test_gateway_endpoint' ) : $this->get_option( 'gateway_endpoint' );
      $this->phone = 'yes' === $this->get_option('phone');
      $this->email = 'yes' === $this->get_option('email');
      $this->ach = 'yes' === $this->get_option( 'ach' );
      $this->card = 'yes' === $this->get_option('card');    
      // This action hook saves the settings
      add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    
      // We need custom JavaScript to obtain a token
      add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );

      // Register a webhook here
      // add_action( 'woocommerce_api_{webhook name}', array( $this, 'webhook' ) );
 		}
 
		/* Plugin options, we deal with it in Step 3 too */
 		public function init_form_fields() {
      $this->form_fields = array(
		    'enabled' => array (
          'title'       => 'Enable / Disable',
          'label'       => 'Enable Red Shepherd Gateway',
          'type'        => 'checkbox',
          'description' => '',
          'default'     => 'no',
          'description' => 'Enable or Disable the Red Shepherd Payment Plugin option',
		    ),
        'card' => array (
          'title'       => 'Enable Card payments',
          'label'       => 'Enable Card payments',
          'type'        => 'checkbox',
          'description' => '',
          'default'     => 'yes',
          'description' => 'This allows Customers to pay using their Credit / Debit card',
		    ),
        'ach' => array (
          'title'       => 'Enable ACH payments',
          'label'       => 'Enable ACH payments',
          'type'        => 'checkbox',
          'description' => '',
          'default'     => 'no',
          'description' => 'This allows Customers to pay using their ACH - Bank Account',
        ),
        'title' => array(
          'title'       => 'Title',
          'type'        => 'text',
          'default'     => 'Red Shepherd Payment Gateway',
          'desc_tip'    => true,
          'description' => 'This controls the title which the user sees during checkout (optional)',
        ),
        'description' => array(
          'title'       => 'Description',
          'type'        => 'textarea',
          'default'     => 'Pay with your credit card',
          'description' => 'This controls the description which the Customer sees during checkout (optional)',
        ),
        'image' => array(
          'title'       => 'Image',
          'label'       => 'Image to display in payment gateway',
          'type'        => 'file',
          'default'     => 'no',
          'description' => 'This will show the an image above the payment gateway (optional)',
        ),
        'phone' => array(
          'title'       => 'Ask for Phone',
          'label'       => 'Ask for Phone Number in the Payment form',
          'type'        => 'checkbox',
          'default'     => 'no'
        ),
        'email' => array(
          'title'       => 'Ask for Email',
          'label'       => 'Ask for Email in the Payment form',
          'type'        => 'checkbox',
          'default'     => 'no'
        ),
        'testmode' => array(
          'title'       => 'Test Mode',
          'label'       => 'Enable Test Mode',
          'type'        => 'checkbox',
          'description' => 'Test the Payment gateway in Test mode using test API keys. These are not real transactions',
          'default'     => 'yes',
          'desc_tip'    => true,
        ),
        'test_app_name' => array(
          'title'       => 'Test App Name',
          'default'     => 'DEMO',
          'type'        => 'text',
          'description' => 'Test App name is DEMO, please contact us at support@redshepherd.com for your Live Account',
        ),
        'test_publishable_key' => array(
          'title'       => 'Test Key',
          'default'     => 'MIIBojANBgkqhkiG9w0BAQEFAAOCAY8AMIIBigKCAYEAtsQxNp3vmKVNYIxfWSi0LIRgCnPaMn0MUNouxgrs4zmg4cnvSeQ3I8YP03YbpXuWA80RvOw/nWErYAKomniJw8Y+xexMfBQ5sgJgewn3ZnRPNM9Y4Z62gwfIlsrs7Bwvpz9uUtLgeQLl1ffNaumnu1IBrqRps0EZ1QyDuu41UckTyo31C40Wez6IbeMfZeusrmPlIWqyBacdviJ5zHCA3zHNq86QMnB8HOP1U81HOSs6GTTelhD7lCoJ+fHKHxcz0MDr37fNpKpC57B0/20wBXFp9tlVtSkHcIty1lyNk2/HDH8knCdqkZk+fCvWgGwdex41x8/rM+LKC13c5J/yG6Gb2PnKhwNk4lvvnz73YAdqTUJ7qNrdtWVnOTWfbMBiNlpBCVqt8xY8UK6u83AVWrWXse0xe2Pn/kRqlXmxWT0mGEoCavjvZ9lQUL7LXAXZ1dff9r+oFUZo6xDQ3ER/OTIKa4jpvaI9S/J1drsrI1f9kkMWFwEh48dCPYplGSxzAgMBAAE=',
          'type'        => 'textarea',
          'description' => 'Use the default DEMO Key provided to test your widget, refer to Documentation for Test Account numbers'
        ),
        'test_gateway_endpoint' => array(
          'title'       => 'Test Gateway Endpoint',
          'default'     => 'https://redpaystable.azurewebsites.net',
          'type'        => 'text',
          'description' => 'Use the provided test gateway to test your plugin'
        ),
        'app_name' => array(
          'title'       => 'Live App Name',
          'type'        => 'text',
          'description' => 'Use your Live App name, contact us support@redshepherd.com for your Live account'
        ),
        'publishable_key' => array(
          'title'       => 'Live Key',
          'type'        => 'textarea',
          'description' => 'Use your Live App key, do not share with anyone'
        ),
        'gateway_endpoint' => array(
          'title'       => 'Live Gateway Endpoint',
          'default'     => '',
          'type'        => 'text',
          'description' => 'Use your Live Gateway endpoint'
        ),
	    ); 
    }
 
		/* You will need it if you want your custom credit card form, Step 4 is about it */
		public function payment_fields() {
 
	    // Display some description before the payment form
	    if ( $this->description ) {
		    // Instructions for test mode, card numbers / test messages etc.
		    if ( $this->testmode ) {
			    $this->description .= ' TEST MODE ENABLED. In test mode, Use the Test Card / Account numbers listed in the documentation';
			    $this->description  = trim( $this->description );
		    }
        
        // display the description with <p> tags etc.
		    echo wpautop( wp_kses_post( $this->description ) );
	    }
 
	    // I will echo() the form, but you can close PHP tags and print it directly in HTML
	    echo '<fieldset id="wc-' . esc_attr( $this->id ) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';
 
	    // Add this action hook if you want your custom gateway to support it
	    do_action( 'woocommerce_credit_card_form_start', $this->id );
    ?>
    <?php 
      if ($this->card && $this->ach):
    ?>
      <div class="form-row form-row-wide"><label>Select Payment type <span class="required">*</span></label>
        <select style="width:100%;" name="rdm_payment_type" id="rdm_payment_type">
          <?php
            if ($this->card) {
          ?>
          <option value="card">Credit Card / Debit Card</option>
          <?php
            }
          ?>
          <?php
            if ($this->ach) {
          ?>
          <option value="ach">ACH Pay by Check</option>
          <?php
            }
          ?>
        </select>
      </div>
    <?php
      else:
    ?>
      <input 
        type="hidden" value="<?php
        if($this->card):
        echo 'card';
        else:
        echo 'ach';
        endif;
      ?>" name="rdm_payment_type" />
    <?php
      endif;
    ?>
    <?php
      if ($this->card):
    ?>
    <div class="card-form">
      <div class="card-wrapper">
      </div>
      <div class="form-row form-row-wide"><label>Card Holder Name <span class="required">*</span></label>
        <input id="rdm_chname" type="text" autocomplete="off" name="rdm_chname">
        <input id="rd_chname" type="hidden" name="rd_chname">
      </div>
      <?php
        if ( $this->phone ) {
      ?>
      <div class="form-row form-row-wide"><label>Card Holder Phone <span class="required">*</span></label>
        <input id="rd_phone" type="text" autocomplete="off" name="rd_phone">
      </div>
      <?php
        }
      ?>
      <?php
        if ( $this->email ) {
      ?>
      <div class="form-row form-row-wide"><label>Card Holder Email <span class="required">*</span></label>
        <input id="rd_email" type="text" autocomplete="off" name="rd_email">
      </div>
      <?php
        }
      ?>
      <script language="javascript" type="text/javascript">
        function limitText(limitField, limitCount, limitNum) {
          if (limitField.value.length > limitNum) {
            limitField.value = limitField.value.substring(0, limitNum);
          } else {
            limitCount.value = limitNum - limitField.value.length;
          }
        }
      </script>
      <div class="form-row form-row-wide">
        <label>Card Number <span class="required">*</span></label>
        <input id="rdm_ccNo" name="rdm_ccNo" class="input-element" autocomplete="off" type="text" onKeyDown="limitText(this.form.rdm_ccNo,this.form.countdown,19);" 
        onKeyUp="limitText(this.form.rdm_ccNo,this.form.countdown,19);" maxlength="19">
        <input id="rd_ccNo" type="hidden" name="rd_ccNo">
      </div>
      <div class="form-row form-row-first">
        <label>Expiry Date <span class="required">*</span></label>
        <input id="rdm_expdate" type="text" autocomplete="off" placeholder="mm / yy" name="rdm_expdate">
        <input id="rd_expdate" type="hidden" name="rd_expdate">
      </div>
      <div class="form-row form-row-last">
        <label>Card Code (CVC) <span class="required">*</span></label>
        <input id="rdm_cvv" type="password" autocomplete="off" placeholder="CVC" name="rdm_cvv">
        <input id="rd_cvv" type="hidden" name="rd_cvv">
      </div>
    </div>
    <?php
      endif;
    ?>
    <?php
      if ($this->ach):
    ?>
    <div class="ach-form">
      <div class="form-row form-row-wide">
        <img style="padding: 0.5rem;max-height:300px;" src="data:image/gif;base64,R0lGODlhEgIPAfcAANLc2Nzx6VpkYPP+8+v389z59OLt6a+7t+z997zIxHaBfLK+utvq5er79en69On8+Ob583yBhGdybez++MPNyPD+/rjDvOv57Or27MbSykVER+b38Lq6vPD++d3u6OX27GBrZ8nS0aGsqJmkne72+KizrOb99ef69Oj58yEjJeP67IeTi3R6d+Px4uDs5VljV+778fD67OHx7On89tTh2+n+8OPt3Zyoou7/9+b88DQ6Ns7Z1OTw7JCbltnk2/X//n+IeOz38+Lz7oyYkYaEiev//SkcJeP7+N/w6u/790hRSDpCNoiIiENLPsPNvOn05FVUWScqGmNqWu7+7/j/+9rm4dzq3REUE9DXyQABAOv6+e7//VJZSaOun252cuz49PT/+V5RWrC5p/P0+e7u7ioxJu7+9uP17+//+3F7a+X06pmZmez/+UxYUR4iEu35+gcaGNTf0n6Lg+n29dzj1ubx6JGZiDI7KNfp4jxFQYKNiM3i2fH28Ob281JaU7G9sHdJRdXm4Ojy9OXz8FRVVFRdWeD16vj+8+z8+ZSfmuDx8BoKFOL48BskIOD+9+r55+z/9i01MXydlBcZCH+QiM3e1+759cXVzn6HhCQXIO//8/D791ZTaef4+NziyrP/6ur78N798NTo2ev19Oz99Ov69/3y9+Dn4OP28+jt5FpfXN7p1VxPWM326dvw4Ob65Oj47wkOClhfTra4uuz49+L78+n/9Oj++LjBso+Ukuz/88G4ver787W4xOzl7O3y7uv8+6Sforyyupeek6qrqtP+9uj49ZaVmq61sX+hkW1MX+3t+e/680svPoFteGyUhBEGC+r/94R5gO37/Oj07+bx8oiPf/D7+/f6+U9PSMnY0szNzBodHIqOjjMzMiYsKpqTmuv48ev89Or59Oz79u79+Or99+j79+v58un59+z79Oj88/D89Oj57+79+u799un68uf+8jw+PpCOju7w9nVxdvbi7vTq8+/4+IuBiaCRlNjs5t3d3eny+f///+v89iH5BAAAAAAALAAAAAASAg8BAAj/AP0JHEiwoMGDCBMqXMiwocOHECNKnEixosWLGDNq3Mixo8ePIEOKHEmypMmTKFOqXMmypcuXMGPKnEmzps2bOHPq3Mmzp8+fQIMKHUq0qNGjSJMqXcq0qdOnGfckIiKtngIv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" alt="ACH" />
      </div>
      <div class="form-row form-row-wide"><label>Account Owner Name <span class="required">*</span></label>
        <input id="rdm_ahname" type="text" autocomplete="off" name="rdm_ahname">
        <input id="rd_ahname" type="hidden" name="rd_ahname">
      </div>
      <div class="form-row form-row-wide"><label>Bank Account Number <span class="required">*</span></label>
        <input id="rdm_baNo" maxlength="16" class="input-element" type="text" autocomplete="off" name="rdm_baNo">
        <input id="rd_baNo" type="hidden" name="rd_baNo">
        </div>
      <div class="form-row form-row-wide"><label>Routing Number <span class="required">*</span></label>
        <input id="rdm_routingNo" class="input-element" type="text" autocomplete="off" name="rdm_routingNo">
        <input id="rd_routingNo" type="hidden" name="rd_routingNo">
        </div>
      <div class="form-row form-row-wide"><label>Select Account Type <span class="required">*</span></label>
        <select style="width:100%;" name="rdm_accountType" id="rdm_accountType">
          <option value="C">Checking</option>
          <option value="S">Savings</option>
        </select>
        <input id="rd_accountType" type="hidden" name="rd_accountType">
      </div>
    </div>
    <?php
      endif;
    ?>
    <div class="clear"></div>
    <?php
      do_action( 'woocommerce_credit_card_form_end', $this->id );
      echo '<div class="clear"></div></fieldset>';
    } // end of public function payment_fields()

    /* Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form */
    public function payment_scripts() {
      //   // we need JavaScript to process a token only on cart/checkout pages, right?
      // 	if ( ! is_cart() && ! is_checkout() && ! isset( $_GET['pay_for_order'] ) ) {
      // 		return;
      // 	}
      
      // 	// if our payment gateway is disabled, we do not have to enqueue JS too
      // 	if ( 'no' === $this->enabled ) {
      // 		return;
      // 	}
      
      // 	// no reason to enqueue JavaScript if API keys are not set
      // 	if ( empty( $this->private_key ) || empty( $this->publishable_key ) ) {
      // 		return;
      // 	}
      
      // 	// do not work with card detailes without SSL unless your website is in a test mode
      // 	if ( ! $this->testmode && ! is_ssl() ) {
      // 		return;
      // 	}
          
      // Add the form data crypto library    
      wp_enqueue_script( 'redshepherd_js', 'https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.2/rollups/aes.js' );
      wp_register_script( 'woocommerce_rd_form', plugins_url( 'rdformhandler.js', __FILE__ ), array( 'jquery' ) );
      wp_enqueue_script( 'woocommerce_rd_form' );
      wp_register_script( 'woocommerce_cleave', plugins_url( 'cleave.min.js', __FILE__ ), array( 'jquery', 'redshepherd_js' ) );
      wp_enqueue_script( 'woocommerce_cleave' );
      
      // And this is our custom JS in your plugin directory that works with token.js
      wp_register_script( 'woocommerce_redshepherd', plugins_url( 'redshepherd.js', __FILE__ ), array( 'jquery', 'redshepherd_js','woocommerce_cleave' ), null, true );
      wp_enqueue_script( 'woocommerce_redshepherd' ); 
    }
    
    /* Fields validation, more in Step 5 */
    public function validate_fields() {
      $rdm_payment_type = $_POST['rdm_payment_type'];

      if($rdm_payment_type === 'ach') {
        if( empty( $_POST[ 'rd_ahname' ]) ) {
          wc_add_notice(  'Account Holder Name is required!', 'error' );
          return false;
        }
        if( empty( $_POST[ 'rd_baNo' ]) ) {
          wc_add_notice(  'Bank Account is required!', 'error' );
          return false;
        }
        if( empty( $_POST[ 'rd_routingNo' ]) ) {
          wc_add_notice(  'Routing Number is required!', 'error' );
          return false;
        }
        if( empty( $_POST[ 'rd_accountType' ]) ) {
          wc_add_notice(  'Account Type is required!', 'error' );
          return false;
        }
      } else {
        $required_check = true;

        if( empty( $_POST[ 'rd_chname' ]) ) {
          wc_add_notice(  'Card Holder Name is required!', 'error' );
          $required_check = false;
          return false;
        }

        if ( $this->phone ) {
          if( empty( $_POST[ 'rd_phone' ]) ) {
            wc_add_notice(  'Card Holder Phone Number is required!', 'error' );
            $required_check = false;
            return false;
          }
        }
        
        if( $this->email ) { 
          if( empty( $_POST[ 'rd_email' ]) ) {
            wc_add_notice(  'Card Holder Email is required!', 'error' );
            $required_check = false;
            return false;
          }
        }  

        if( empty( $_POST[ 'rd_ccNo' ]) ) {
          wc_add_notice(  'Card Number is required!', 'error' );
          $required_check = false;
          return false;
        }
        
        if( empty( $_POST[ 'rd_expdate' ]) ) {
          wc_add_notice(  'Expiration Date is required!', 'error' );
          $required_check = false;
          return false;
        }
        
        if( empty( $_POST[ 'rd_cvv' ]) ) {
          wc_add_notice(  'CVV is required!', 'error' );
          $required_check = false;
          return false;
        }
        
        if($required_check) {
          //$myPassword = "w#bT*28YLAB4cG";
          $rd_expdate =(string) base64_decode($_POST[ 'rd_expdate' ]);
          $rd_ccNo = base64_decode($_POST[ 'rd_ccNo' ]);
          echo $rd_ccNo;
          $exp_pattern = '~^(0[1-9]|1[0-2])([12]\d{3})$~';
          //if(!preg_match(trim($rd_expdate), $match)){
          //  wc_add_notice(  'Expire date should be mmyyyy format', 'error' );
          // 	return false;
          // }
          if(!($this -> is_valid_luhn($rd_ccNo))) {
            wc_add_notice(  'Invalid Card Number', 'error' );
            return false;
          }
        }
      }
      return true; 
    }
    
    /* Luhn Check Algorithm */
    public function is_valid_luhn($number) {
      settype($number, 'string');
      $sumTable = array(
        array(0,1,2,3,4,5,6,7,8,9),
        array(0,2,4,6,8,1,3,5,7,9));
      $sum = 0;
      $flip = 0;
      for ($i = strlen($number) - 1; $i >= 0; $i--) {
        $sum += $sumTable[$flip++ & 0x1][$number[$i]];
      }
      return $sum % 10 === 0;
    }
 
    /* We're processing the payments here, everything about it is in Step 5 */
    public function process_payment( $order_id ) {
      global $woocommerce;
 
      $order = wc_get_order( $order_id ); // we need it to get any order details
      $rsaKey = $this->publishable_key;
      $rsaPublicKey = <<<EOD
-----BEGIN PUBLIC KEY-----
$this->publishable_key
-----END PUBLIC KEY-----
EOD;

      $rdm_payment_type = $_POST['rdm_payment_type'];

      if($rdm_payment_type === 'ach') {
        $rd_ahname = base64_decode($_POST['rd_ahname']);
        $rd_baNo = base64_decode($_POST['rd_baNo']);
        $rd_routingNo = base64_decode($_POST['rd_routingNo']);
        $rd_accountType = base64_decode($_POST['rd_accountType']);
        $data_request = array("account" => $rd_baNo,
                              "ref1" => $order_id,
                              "accountType" => $rd_accountType,
                              "routing" => $rd_routingNo,
                              "amount" => $order->get_total()*100,
                              "cardHolderName" => $rd_ahname,
                              "action" => "A",
                              "currency" => $order->get_currency(),
                              "method" => "ACH",
                              "productRef" => "E");
        $cipherKey = $this->encrypt(json_encode($data_request), $rsaPublicKey);
        $request_packet = array("app" => $this->app_name, "data" => $cipherKey);
        $dataResponse = wp_remote_post( $this->gateway_endpoint.'/wach', array(
            'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
            'body'        => json_encode($request_packet),
        ) );
        if( !is_wp_error( $dataResponse ) ) {
          $response_body = json_decode( $dataResponse['body'], true );
          $card_response = json_decode($response_body, true);

          if ($response_body['data']['responseCode'] == 'A'){
            // we received the payment
            $order->payment_complete();
            $order->reduce_order_stock();

            // some notes to customer (replace true with false to make it private)
            $order->add_order_note( 'Your order is paid! Thank you!', true );

            // Empty cart
            $woocommerce->cart->empty_cart();

            // Redirect to the thank you page
            return array(
              'result' => 'success',
              'redirect' => $this->get_return_url( $order )
            );
          } else {
              wc_add_notice(  'Please try again.', 'error' );
              return;
          }
        } else {
            wc_add_notice(  'Connection error.', 'error' );
            return;
        }
      }else{
        $rd_ccNo = base64_decode($_POST[ 'rd_ccNo' ]);
        $rd_chname = base64_decode($_POST[ 'rd_chname' ]);
        $rd_expdate = base64_decode($_POST[ 'rd_expdate' ]);
        $rd_cvv = base64_decode($_POST[ 'rd_cvv' ]);
        $data_request = array("account" => $rd_ccNo,
                              "ref1" => $order_id,
                              "amount" => $order->get_total()*100,
                              "expmmyyyy" => $rd_expdate,
                              "cvv" => $rd_cvv,
                              "cardHolderName" => $rd_chname,
                              "avsZip" => $woocommerce->customer->get_shipping_postcode(),
                              "cardHolderPhone" => $_POST[ 'rd_phone' ],
                              "cardHolderEmail" => $_POST[ 'rd_email' ],
                              "action" => "A",
                              "currency" => $order->get_currency(),
                              "method" => "CNP",
                              "productRef" => "E");
        $cipherKey = $this->encrypt(json_encode($data_request), $rsaPublicKey);
        $request_packet = array("app" => $this->app_name, "data" => $cipherKey);
        $dataResponse = wp_remote_post( $this->gateway_endpoint.'/wcard', array(
            'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
            'body'        => json_encode($request_packet),
        ) );
        if( !is_wp_error( $dataResponse ) ) {
          $response_body = json_decode( $dataResponse['body'], true );
          $card_response = json_decode($response_body, true);
            
          if ($response_body['data']['responseCode'] == 'A'){
            // we received the payment
            $order->payment_complete();
            $order->reduce_order_stock();

            // some notes to customer (replace true with false to make it private)
            $order->add_order_note( 'Your order is paid! Thank you!', true );

            // Empty cart
            $woocommerce->cart->empty_cart();

            // Redirect to the thank you page
            return array(
              'result' => 'success',
              'redirect' => $this->get_return_url( $order )
            );
          } else {
            echo json_encode($card_response);
              wc_add_notice(  'Please try again.', 'error' );
              return;
          }
        } else {
            wc_add_notice(  'Connection error.', 'error' );
            return;
        }
      }
    }
 
		/* In case you need a webhook  */
		public function webhook() {}
    
    /* Generate RSA Text */
    public function encrypt($data, $key) {
      $encryptedText = '';
      if (openssl_public_encrypt($data, $encryptedText, $key)) {
        $encryptedText = $encryptedText;
      }
      else{
          throw new Exception('Unable to encrypt data. Perhaps it is bigger than the key size?');
      }
      return base64_encode($encryptedText);
    }

 	} // end of class WC_RedShepherd_Gateway extends WC_Payment_Gateway
} // end of function redshepherd_init_gateway_class()