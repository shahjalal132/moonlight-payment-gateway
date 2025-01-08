<?php

$api_url      = get_option( 'api_url' );
$api_key      = get_option( 'api_key' );
$security_key = get_option( 'security_key' );

?>

<h4 class="common-title">API Credentials</h4>

<div class="credentials-wrapper overflow-hidden">
    <div class="common-input-group">
        <label for="api_url">User</label>
        <input type="text" class="common-form-input" name="api_url" id="api_url" placeholder="User"
            value="<?= $api_url ?>" required>
    </div>
    <div class="common-input-group mt-20">
        <label for="api_key">Password</label>
        <input type="text" class="common-form-input" name="api_key" id="api_key" placeholder="Password"
            value="<?= $api_key ?>" required>
    </div>
    <div class="common-input-group mt-20">
        <label for="api_key">Security Key</label>
        <input type="text" class="common-form-input" name="security_key" id="security_key" placeholder="Security Key"
            value="<?= $security_key ?>" required>
    </div>

    <button type="button" class="save-btn mt-20 button-flex" id="save_credentials">
        <span>Save</span>
        <span class="spinner-loader-wrapper"></span>
    </button>
</div>