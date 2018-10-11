
$(function () {
  login_info();
  check_jwt();
  getAllDevices();
});

var allDevices = [];

function onGetDevicesSuccess(resp) {
   resp.data.forEach(function(x, index, a){
	  allDevices.push(x);
   });
   console.log(allDevices);
   sessionStorage.setItem('allDevices', JSON.stringify(allDevices));
   if( resp.hasNext){
      getNextPageDevices(4, resp.nextPageLink.idOffset,resp.nextPageLink.textOffset,onGetDevicesSuccess,function(data){}); 
   }
}

function getAllDevices() {
   allDevices = [];
   getDevices(4,onGetDevicesSuccess, function(data){});
}

function check_jwt() {
    if(null == sessionStorage.getItem('jwt_token_decoded')) {
      window.location = "login.html";
    }
}

function login_info() {
	console.log(sessionStorage.getItem('jwt_token'));
    console.log(sessionStorage.getItem('refresh_token'));
	console.log(sessionStorage.getItem('jwt_token_decoded'));
}
