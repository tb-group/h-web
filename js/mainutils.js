
$(function () {
  //login_info();
  //check_jwt();
  //getAllDevices();
  getAllProjectInfo();
});

var allDevices = [];
var allProjects = [];

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
   getDevices(4,onGetDevicesSuccess, function(data){ window.location = "login.html";});
}

function onGetProjectInfoSuccess(resp) {
   resp.forEach(function(x, index, a){
	  allProjects.push(x);
   });
   sessionStorage.setItem('allProjecInfo', JSON.stringify(allProjects));
}

function getAllProjectInfo() {
   allProjects = [];
   getProjects(4,onGetProjectInfoSuccess, function(data){ window.location = "login.html";});
}

function check_jwt() {
    if(null == sessionStorage.getItem('jwt_token_decoded')) {
      window.location = "login.html";
    }
}

function onGetTelemetrysTimeseriesSuccess(resp) {
   deviceTelemetrys = [];
   resp.forEach(function(x){
	  deviceTelemetrys.push(x);
   });
   console.log(resp);
   sessionStorage.setItem('telemetrysTimeseries', JSON.stringify(deviceTelemetrys));
}

function getDeviceTelemetrys(device) {
   getTelemetrysTimeseries(device , 'before=1535904000&length=3600', onGetTelemetrysTimeseriesSuccess, function(data){console.log("error");});
}

function login_info() {
    console.log(sessionStorage.getItem('jwt_token'));
    console.log(sessionStorage.getItem('refresh_token'));
    console.log(sessionStorage.getItem('jwt_token_decoded'));
}
