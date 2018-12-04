
var baseUrl = '/h-web';

function Base64URLDecode(base64UrlEncodedValue) {
    var result1;
    var result2;
    var newValue = base64UrlEncodedValue.replace("-", "+").replace("_", "/");

    try {
        result1 = window.atob(newValue);
        result2 = decodeURIComponent(escape(window.atob(newValue)));
        if (result1 !== result2) {
           console.log(['_trackEvent', 'error_prevented', 'unicode decode']);
        }
    } catch (e) {
        throw "Base64URL decode of JWT segment failed";
    }

    return result2;
}

function decodeJWT(jwt) {
    var segments = jwt.split('.');

    if (jwt === "") {
        return "";
    }

    if (segments.length !== 3) {
        throw "JWT is required to have three segments";
    }

    var header = Base64URLDecode(segments[0]);
    var jwtInfo = Base64URLDecode(segments[1]);

    console.log(header);
    console.log(jwtInfo);

    sessionStorage.setItem('jwt_token_decoded', (jwtInfo));
}

function login() {
    var usr=document.getElementById("txtUserName").value;
    var pwd=document.getElementById("pwd").value;
    sessionStorage.removeItem('jwt_token');
    sessionStorage.removeItem('refresh_token');
    sessionStorage.removeItem('jwt_token_decoded');
    $.ajax({
        type: "POST",
        url: "/h-web/authenticate" ,
        dataType: "json",
        data: JSON.stringify({user_login:usr, user_password:pwd}),
        contentType: "application/json",
        success: function (result) {
            if (result.token) {
                sessionStorage.setItem('jwt_token', result.token);
                //sessionStorage.setItem('refresh_token', result.refreshToken);
                decodeJWT(result.token);
                alert("SUCCESS");
                window.location = "index.html";
            }else {
                $.alert({ title: '警告!' , content: '用户名密码错!',});
            }
        },
        error : function() {
            alert("error");
        }
    });
}

function login_info() {
    console.log(sessionStorage.getItem('token'));
    console.log(sessionStorage.getItem('jwt'));
}

function updateAndValidateToken(token, prefix, notify) {
    var valid = false;
    var tokenData = decodeJWT(token);
    var issuedAt = tokenData.iat;
    var expTime = tokenData.exp;
    if (issuedAt && expTime) {
        var ttl = expTime - issuedAt;
        if (ttl > 0) {
            var clientExpiration = new Date().valueOf() + ttl*1000;
            store.set(prefix, token);
            store.set(prefix + '_expiration', clientExpiration);
            valid = true;
        }
    }
    if (!valid && notify) {
        window.location = "login.html";
    }
}

function getDevices(limit, success_fun, error_fun) {
  var devicesUrl = '/api/tenant/devices?limit='+limit;
  var token = sessionStorage.getItem('jwt_token');
  $.ajax({
      type: 'GET',
      dataType: 'json',
      url: devicesUrl,
      headers: {'X-Authorization': 'Bearer '+token},
      success: function(data) {
         success_fun(data);
      },
      error:function(data){
        error_fun(data);
          alert("error");
      }
   });
}

function getNextPageDevices (limit, idOffset, textOffset, success_fun, error_fun) {
  var devicesUrl = '/api/tenant/devices?limit='+limit+'&idOffset='+idOffset+'&textOffse='+textOffset;
  var token = sessionStorage.getItem('jwt_token');
  var resp;
  $.ajax({
         type: 'GET',
         dataType: 'json',
         url: devicesUrl,
         headers: {'X-Authorization': 'Bearer '+token},
         success: function(data) {
         success_fun(data);
         },
         error:function(data){
        error_fun(data);
         alert("error");
       }
  });
}

function getProjects(limit, success_fun, error_fun) {
  var devicesUrl = baseUrl + '/getProjectInfo';
  var token = sessionStorage.getItem('jwt_token');
  $.ajax({
    type: 'GET',
    dataType: 'json',
    url: devicesUrl,
    headers: {'X_Authorization': token},
    success: function(data) {
         console.log(data);
         success_fun(data);
    },
    error:function(data){
        error_fun(data);
      }
  });
}

function getTelemetrysTimeseries(devices, param,success_fun, error_fun) {
  var devicesUrl = baseUrl + '/telemetry/' + devices;
  if(param !== '') { devicesUrl += '?' + param; }
  var token = sessionStorage.getItem('jwt_token');
  $.ajax({
    type: 'GET',
    dataType: 'json',
    url: devicesUrl,
    headers: {'X_Authorization': token},
    success: function(resp) {
         success_fun(resp);
    },
    error:function(resp){
        error_fun(resp);
      }
  });
}

function getTableColumns(table_name, success_fun, error_fun) {
  var devicesUrl = baseUrl + '/table_columns/'+ table_name;
  var token = sessionStorage.getItem('jwt_token');
  $.ajax({
    type: 'GET',
    dataType: 'json',
    url: devicesUrl,
    headers: {'X_Authorization': token},
    success: function(data) {
         console.log(data);
         success_fun(data);
    },
    error:function(data){
        error_fun(data);
      }
  });
}

function getTableData(table_name, success_fun, error_fun) {
  var devicesUrl = baseUrl + '/table_data/'+ table_name;
  var token = sessionStorage.getItem('jwt_token');
  $.ajax({
    type: 'GET',
    dataType: 'json',
    url: devicesUrl,
    headers: {'X_Authorization': token},
    success: function(data) {
         success_fun(data);
    },
    error:function(data){
        error_fun(data);
      }
  });
}