function ready(fn) {
    if (document.readyState != 'loading'){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}

function formSetup(formId, callType){
    document.getElementById(formId).addEventListener('submit', function(e){
        window['http' + callType](e, undefined, undefined, function(data){
            console.log(data.links.redirect);
            window.location.replace(data.links.redirect);
        }, function(error, status){
            document.getElementById('errorMessage').innerText = error.title;
            console.log(error.title);
        })
    });
}

function httpGet(url, successCallback, failCallback) {
    var request = new XMLHttpRequest();
    request.open('GET', url, true);
    request.setRequestHeader('Authorization', 'Bearer bearerDefault');

    request.onload = function() {
        if (this.status >= 200 && this.status < 400) {
            successCallback(this.response);
        } else {
            if (failCallback !== undefined){
                failCallback(this.status);
            }
        }
    };

    request.onerror = function() {
        failCallback();
    };

    request.send();
}

var serialize = function (form) {
    var field,
        l,
        s = [];

    if (typeof form == 'object' && form.nodeName == "FORM") {
        var len = form.elements.length;

        for (var i = 0; i < len; i++) {
            field = form.elements[i];
            if (field.name && !field.disabled && field.type != 'button' && field.type != 'file' && field.type != 'reset' && field.type != 'submit') {
                if (field.type == 'select-multiple') {
                    l = form.elements[i].options.length;

                    for (var j = 0; j < l; j++) {
                        if (field.options[j].selected) {
                            s[s.length] = encodeURIComponent(field.name) + "=" + encodeURIComponent(field.options[j].value);
                        }
                    }
                }
                else if ((field.type != 'checkbox' && field.type != 'radio') || field.checked) {
                    s[s.length] = encodeURIComponent(field.name) + "=" + encodeURIComponent(field.value);
                }
            }
        }
    }
    return s.join('&').replace(/%20/g, '+');
};

function httpPost(e, url, data, successCallback, failCallback) {
    httpCall(e, 'POST', url, data, successCallback, failCallback);
}

function httpPatch(e, url, data, successCallback, failCallback) {
    httpCall(e, 'PATCH', url, data, successCallback, failCallback);
}

function httpPut(e, url, data, successCallback, failCallback) {
    httpCall(e, 'PUT', url, data, successCallback, failCallback);
}

function httpDelete(e, url, data, successCallback, failCallback) {
    httpCall(e, 'DELETE', url, data, successCallback, failCallback);
}

function httpCall(e, method, url, data, successCallback, failCallback) {
    if (e !== undefined) {
        e.preventDefault();
        if (url === undefined){
            url = e.target.action;
        }
    }

    var request = new XMLHttpRequest();
    request.open(method, url, true);

    if (data === undefined && e !== undefined){
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        data = serialize(e.target);
    } else {
        request.setRequestHeader('Content-Type', 'application/json');
        data = JSON.stringify(data);
    }

    request.onload = function() {
        if (this.status >= 200 && this.status < 400) {
            if (this.response == ''){
                successCallback({}, this.status);
            } else {
                successCallback(JSON.parse(this.response), this.status);
            }
        } else {
            if (failCallback !== undefined) {
                failCallback(JSON.parse(this.response).errors[0], this.status);
            }
        }
    };

    request.send(data);
}