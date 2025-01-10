<?php

$api_url           = get_option( 'api_url' );
$api_key           = get_option( 'api_key' );
$test_security_key = get_option( 'test_security_key' );
$live_security_key = get_option( 'test_security_key' );

?>

<h4 class="common-title">API Credentials</h4>

<div class="credentials-wrapper overflow-hidden">
    <div class="common-input-group">
        <label for="api_url">User</label>
        <input type="text" class="common-form-input" name="api_url" id="api_url" placeholder="User"
            value="<?= $api_url ?>" >
    </div>
    <div class="common-input-group mt-20">
        <label for="api_key">Password</label>
        <input type="text" class="common-form-input" name="api_key" id="api_key" placeholder="Password"
            value="<?= $api_key ?>" >
    </div>
    <div class="common-input-group mt-20">
        <label for="test_security_key">Test Security Key</label>
        <input type="text" class="common-form-input" name="test_security_key" id="test_security_key"
            placeholder="Test Security Key" value="<?= $test_security_key ?>" required>
    </div>
    <div class="common-input-group mt-20">
        <label for="live_security_key">Live Security Key</label>
        <input type="text" class="common-form-input" name="live_security_key" id="live_security_key"
            placeholder="Live Security Key" value="<?= $live_security_key ?>" required>
    </div>

    <button type="button" class="save-btn mt-20 button-flex" id="save_credentials">
        <span>Save</span>
        <span class="spinner-loader-wrapper"></span>
    </button>
</div>