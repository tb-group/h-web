
$(function () {
   iniMap();
});

var map;
var markers = [];

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

function setMarkers(points) {
   markers = [];
   for ( var i = 0; i <points.length; i++){
      var content = "<div style='font-size: 10px;'> <table>";  
          //content = content + "<tr><td align="right"><b>噪声:</b> </td><td>${leq} dB</td><td align="right"><b>TSP:</b> </td><td>${tsp} 毫克/立方米</td></tr>";  
          //content = content + "<tr><td align="right"><b>湿度:</b></td><td> ${humudata} %</td><td align="right"><b>温度:</b></td><td> ${temperature} °C</td></tr>"; 
          //content = content + "<tr><td align="right"><b>风向:</b></td><td> ${winddirect}</td><td align="right"><b>风速:</b></td><td> ${windspeed} 米/秒</td></tr>"; 
          content = content + "<tr></tr></table><br/><table><tr><td>总承包商:"+points[i].contractors+"</td><td>地址:"+points[i].address+"</td></tr>";
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

function createMark(node, info_html){
        var _marker = new BMap.Marker(new BMap.Point(node.longitude, node.latitude));
        _marker.addEventListener("click", function(e){
            this.openInfoWindow(new BMap.InfoWindow(info_html));
        });
        return _marker;
};
