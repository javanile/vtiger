$(function() {


    $('#ex1f').instaFilta({
        scope: '#ex1'
    });


    $('#ex2f').instaFilta({
        scope: '#ex2'
    });


    $('#ex3f').instaFilta({
        scope: '#ex3',
        targets: '.planet-name',
        caseSensitive: true
    });


    $('#ex4f').instaFilta({
        scope: '#ex4',
        markMatches: true
    });


    $('#ex5f').instaFilta({
        scope: '#ex5',
        beginsWith: true
    });


    $('#ex6f').instaFilta({
        scope: '#ex6',
        beginsWith: true
    });


    var $resultMessage = $('#some-result-message');

    $('#ex7f').instaFilta({
        scope: '#ex7',
        onFilterComplete: function(matchedItems) {

            var message = matchedItems.length 
                ? "I found " + matchedItems.length + " matches!"
                : "I couldn't find a thing..";

            $resultMessage.text(message);
        }
    });


    var ex8 = $('#ex8f').instaFilta({
        scope: '#ex8'
    });

    $('#ex8s').on('change', function() {
        ex8.filterCategory($(this).val());
    });


    var ex9 = $('#ex9f').instaFilta({
        scope: '#ex9'
    });

    var $ex9Checkboxes = $('#ex9 [type=checkbox]');

    $ex9Checkboxes.on('change', function() {

        var checkedCategories = [];

        $ex9Checkboxes.each(function() {
            if ($(this).prop('checked')) {
                checkedCategories.push($(this).val());
            }
        });

        ex9.filterCategory(checkedCategories);
    });


    var ex10 = $('#ex10f').instaFilta({
        scope: '#ex10'
    });

    var $ex10Checkboxes = $('#ex10 [type=checkbox]');

    $ex10Checkboxes.on('change', function() {

        var checkedCategories = [];

        $ex10Checkboxes.each(function() {
            if ($(this).prop('checked')) {
                checkedCategories.push($(this).val());
            }
        });

        ex10.filterCategory(checkedCategories, true);
    });

});