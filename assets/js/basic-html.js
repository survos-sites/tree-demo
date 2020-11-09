require('jstree');
const $ = require('jquery');

$('#html1').jstree(
    {
        "plugins": ['checkbox', 'theme', "html_data", "types"]
    }
);
