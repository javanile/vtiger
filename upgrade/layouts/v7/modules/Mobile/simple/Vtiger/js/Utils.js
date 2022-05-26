/* 
 * UTILITES
 */

mobileapp.filter('nospace', function () {
    return function (value) {
        return (!value) ? '' : value.replace(/ /g, '');
    };
});



mobileapp.config(function ($mdThemingProvider) {


    $mdThemingProvider.definePalette('marketing', $mdThemingProvider.extendPalette('red', {
        'A100': '#f44336'
    }));
    $mdThemingProvider.definePalette('sales', $mdThemingProvider.extendPalette('green', {
        'A100': '#4caf50'
    }));
    $mdThemingProvider.definePalette('projectmgmt', $mdThemingProvider.extendPalette('deep-purple', {
        'A100': '#673ab7'
    }));
    $mdThemingProvider.definePalette('support', $mdThemingProvider.extendPalette('indigo', {
        'A100': '#3f51b5'
    }));
    $mdThemingProvider.definePalette('inventory', $mdThemingProvider.extendPalette('amber', {
        'A100': '#ffc107',
        // whether, by default, text (contrast)
        // on this palette should be dark or light
        'contrastDefaultColor': 'light',
        //hues which contrast should be 'dark' by default
        'contrastDarkColors': ['50', '100', '200', '300', '400'],
        // could also specify this if default was 'dark'
        'contrastLightColors': ['500', '600', '700', '800', '900', 'A200', 'A400', 'A700']
    }));

    $mdThemingProvider.theme('SALES').primaryPalette("sales");
    $mdThemingProvider.theme('MARKETING').primaryPalette("marketing");
    $mdThemingProvider.theme('PROJECT').primaryPalette("projectmgmt");
    $mdThemingProvider.theme('SUPPORT').primaryPalette("support");
    $mdThemingProvider.theme('INVENTORY').primaryPalette("inventory");
    $mdThemingProvider.alwaysWatchTheme(true);
});