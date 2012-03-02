var select = $('#id_discussion_id');

var all_options = select.children();

function limit_discussions() {
    select.children().detach();

    var forum_id = $('#id_forum_id option:selected').val();

    select.append(all_options.filter('[value="0"]'));
    select.append(all_options.filter('[value^="' + forum_id + '_"]'));
}

limit_discussions();

$('#id_forum_id').change(function() {
    limit_discussions();
});
