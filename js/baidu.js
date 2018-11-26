
$(function () {
   iniMap();
});

var map;
var markers = [];
var infoWindowcbf;

function setInfoWindowOnclick(cbfun) {
   infoWindowcbf = cbfun;
}

function openImageWind(url) {
   window.open(url,'','width=450,height=240,top=100, left=300'); 
} 

function iniMap() {
   //创建Map实例
   map = new BMap.Map("container11");
   map.centerAndZoom("上海",10);
   //添加鼠标滚动缩放
   map.enableScrollWheelZoom();
   //添加缩略图控件
   map.addControl(new BMap.OverviewMapControl({isOpen:false,anchor:BMAP_ANCHOR_BOTTOM_RIGHT}));
   //添加缩放平移控件
   map.addControl(new BMap.NavigationControl());
   //添加比例尺控件
   map.addControl(new BMap.ScaleControl());
   //添加地图类型控件
   map.addControl(new BMap.MapTypeControl());
}

function godashboard(device_name) {
   console.log(device_name);
   infoWindowcbf(device_name);
}

function setMarkers(points) {
   removeMarkers();
   markers = [];
   for ( var i = 0; i <points.length; i++){
      var content = "<div style='font-size: 10px;'> <table>";
          content = content + "<tr><td><a href='javascript:void(0)' onclick=\"godashboard('"+points[i].devname+"')\">"+ points[i].devname + "</a></td></tr>";
          content = content + "<tr><td><b>噪声:</b>"+ points[i].last_noisevalue +"dB</td><td><b>TSP:</b>"+points[i].last_tspvalue +"毫克/立方米</td></tr>";  
          content = content + "<tr><td><b>湿度:</b>"+ points[i].last_humidvalue +"%</td><td><b>温度:</b>"+ points[i].last_tempvalue +"°C</td></tr>"; 
          content = content + "<tr><td><b>风向:</b>"+ points[i].last_winddirection +"</td><td><b>风速:</b>"+ points[i].last_windspeed +"米/秒</td></tr>"; 
          content = content + "<tr><td>总承包商:"+points[i].contractors+"</td><td>地址:"+points[i].address+"</td></tr>";
          content = content + "<tr><td>负责人:"+points[i].prjmanager+"</td><td>电话:"+points[i].telephone+"</td></tr>";
          //content = content + "<tr><td><Button onClick=openImageWind('${imageUrl}')> 现场图片</Button></td><td></td></tr>";
          content += "</table></div>";
      
      var marker = createMark(points[i], content);
      markers.push(marker);
   }
   refreshMarkers();
}

function refreshMarkers() {
   for ( var i = 0; i <markers.length; i++){
      map.addOverlay(markers[i]);
   }
}

function removeMarkers() {
   for ( var i = 0; i <markers.length; i++){
      map.removeOverlay(markers[i]);
   }
}

function createMark(node, info_html){
    var _marker = new BMap.Marker(new BMap.Point(node.longitude, node.latitude));
    _marker.addEventListener("click", function(e){
         this.openInfoWindow(new BMap.InfoWindow(info_html));
    });
    return _marker;
};
