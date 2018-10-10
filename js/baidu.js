
$(function () {
   iniMap();
});

function iniMap() {
   //创建Map实例
   var map = new BMap.Map("container11");
   var point = new BMap.Point(113.416982,23.178147);
   map.centerAndZoom(point,18);
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
   
   //设置标注的图标
   var icon = new BMap.Icon("img/icon.jpg",new BMap.Size(100,100));
   //设置标注的经纬度
   var marker = new BMap.Marker(new BMap.Point(113.416982,23.178147),{icon:icon});
   //把标注添加到地图上
   map.addOverlay(marker);
   var content = "<table>";  
       content = content + "<tr><td> 编号：001</td></tr>";  
       content = content + "<tr><td> 地点：广州</td></tr>"; 
       content = content + "<tr><td> 时间：2016-12-07</td></tr>";  
       content += "</table>";
   var infowindow = new BMap.InfoWindow(content);
   marker.addEventListener("click",function(){
       this.openInfoWindow(infowindow);
   });
   
   //点击地图，获取经纬度坐标
   map.addEventListener("click",function(e){
       document.getElementById("aa").innerHTML = "经度坐标："+e.point.lng+" &nbsp;纬度坐标："+e.point.lat;
   });
}
