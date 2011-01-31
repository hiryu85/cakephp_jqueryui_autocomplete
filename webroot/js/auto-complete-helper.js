function split( val ) { 
    var tmp = []; 
    var keywords = unescape(val).split(/,\s*/);
    //console.log('Init with string: "'+jQuery.trim(val)+'" => '+keywords);
    jQuery.each(keywords, function (index, value) {
        var value = jQuery.trim(value).replace(/(^\+|\+$)/, '');
        if (value != "" || value != "," || value != "%2C") {
             tmp.push(value); 
             console.log('Adding:  '+value);
       }
    });
    console.log(tmp); 
    return tmp; 
}
function extractLast( term ) { return split( term ).pop(); } 

function tokenize(string, char) {
    var json = {};
    tokens = string.split(char);
    for(token in tokens) {
        var tmp = tokens[token].split('=');
        var token = tmp[0];
        var value = tmp[1];   
        json[token] = value; 
    }
    return json;
}

function Json2QueryString(data) {
    var qstring = [];
    jQuery.each(data, function (key, value) { qstring.push(key+'='+value); });
    return qstring.join('&');
}
