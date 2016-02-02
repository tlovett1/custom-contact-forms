( function( $ ) {

	var $assetRestrictions = $( document.querySelectorAll( '.ccf-asset-restrictions' ) );
	var assetRestrictionsWrap = document.querySelectorAll( '.ccf-asset-loading-restrictions-wrap' )[0];
	var $assetRestrictionEnabled = $( document.querySelectorAll( '.ccf-asset-loading-restriction-enabled' ) );
	var nextKey = $assetRestrictions.find( '.asset' ).length;

	$assetRestrictionEnabled.on( 'change', function( event ) {
		if ( '0' === event.target.value ) {
			assetRestrictionsWrap.className = 'ccf-asset-loading-restrictions-wrap ccf-hide-field';
		} else {
			assetRestrictionsWrap.className = 'ccf-asset-loading-restrictions-wrap';
		}
	} );

	$assetRestrictions.on( 'click', '.add', function( event ) {
		var newAsset = event.target.parentNode.cloneNode( true );
		var location = newAsset.querySelectorAll( '.asset-location' )[0];
		var type = newAsset.querySelectorAll( '.restriction-type' )[0];

		location.value = '';
		type.value = 'url';

		location.name = 'ccf_settings[asset_loading_restrictions][' + nextKey + '][location]';
		type.name = 'ccf_settings[asset_loading_restrictions][' + nextKey + '][type]';
		nextKey++;

		$assetRestrictions.append( $( newAsset ) );
	} );

	$assetRestrictions.on( 'click', '.delete', function( event ) {
		var assets = document.querySelectorAll( '.ccf-asset-restrictions .asset' );

		if ( assets.length < 2 ) {
			event.target.parentNode.querySelectorAll( '.asset-location' )[0].value = '';
			event.target.parentNode.querySelectorAll( '.restriction-type' )[0].value = 'url';
		} else {
			event.target.parentNode.parentNode.removeChild( event.target.parentNode );
		}
	} );

} )( jQuery );