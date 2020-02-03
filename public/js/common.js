function resetDataOldFormSearch(objectDataOld, elementIDFrom) {
    var search_form = elementIDFrom;
    var objectDataFrom = $.extend({}, objectDataOld, true);
    var searchForm = search_form.serializeArray();

    $.each(searchForm, function (index, element) {
        if (objectDataFrom.hasOwnProperty(element.name)) {
            if (objectDataFrom[element.name] != element.value) {
                search_form.find("input[name='" + element.name + "']").val(objectDataFrom[element.name]);
            }
        }
    });
}

function clickCheckbox(indexClick) {
    var confirm_change = $('input[name=confirm_change]')
    var elemCheckBox = $('#click-box-' + indexClick);

    confirm_change.each(function (index, item) {
        if (indexClick == index) {
            var boolCheckBox = !$(elemCheckBox).is(':checked')
            $(item).prop('checked', boolCheckBox);
            $(item).closest('tr').toggleClass('sky-blue', boolCheckBox)
        } else {
            confirm_change[index].checked = false
            $(item).closest('tr').removeClass('sky-blue')
        }
    });
}
