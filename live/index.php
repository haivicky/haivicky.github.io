<!doctype html>
<html><head>
    <meta charset="utf-8">
    <title>Video Live</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Trang Live Stream của Hải">
    <meta name="author" content="Nguyen Huu Dat - J2Team community">

    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" />
    <link href="css/main.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/barrager.css">

   
    <style type="text/css">
      body {
        padding-top: 10px;
      }
    </style>

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  

 
  </head>
  <body style="padding-top: 0px">
  
  	<!-- NAVIGATION MENU -->

    <div class="container" style="width: 1280px; padding-left: 0px">

	  <!-- FIRST ROW OF BLOCKS -->     
      <div class="row">
      	
      	<div class="col-sm-12" style="padding-right: 5px; padding-bottom: 5px">
			<div class="half-unit" style="height: 720px">
	      		<video style="width: 100%" id="player">
				<source id="audio_src" src="video/1.mp4" type="video/mp4" />
				</video>
	      		
			</div>

        </div>
        
      <!-- USER PROFILE BLOCK -->
        
      </div><!-- /row -->
      <style>
      	#laggg{
      		position: fixed;
	        top: 250px;
	        left: 400px;
	        background-color: #FFFFFF;
	        z-index: 99999;
	        opacity: 1;
	        filter: alpha(opacity=100);
	        -moz-opacity: 1;
	        height: 100px;
	        width: 280px;
	        -moz-border-radius: 7px;
	        border-radius: 7px;
	        text-align: center;
	        color: green;
	        display: none;
      	}
      </style>
      <div id="laggg">
      	<img src="images/load2.gif" /><br/>
      	<strong>Laggg Gùi chờ chút nha! mạng cùi quá...</strong>
      </div>
      
	</div> <!-- /container -->
	
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>    
     <script type="text/javascript" src="js/jquery.barrager.js"></script>  
     <script type="text/javascript" src="js/localstoragedb.min.js"></script>  
     
     <script type="text/javascript">
	 
		/////// Cấu hình live ////////
		var token = 'EAAA...'; //token có quyền xem bài
		var idpost = '6780....'; //ID bài live
		var getcmt_time = 3; //thời gian get cmt mới (s)
		var showcmt_time = 4; //thời gian hiển thị cmt (s)
		var soluongvideo = 1; //Số lượng video sẽ play, file video sẽ nằm trong thư mục video theo thứ tự 1.mp4, 2.mp4,.....
		var mute_video = false; //có phát âm thanh của video ko? dành cho bạn nào dùng âm thành ngoài
		//////// end cau hinh ////////
		
		var db = new localStorageDB("video_live", localStorage);
		if(!db.tableExists('comments') ) {
			db.createTable("comments", ["idcmt", "uid", "message", "name","show"]);
			db.commit();
		}

		$( "#player" ).click(function() {
		  window.location="index.php";
		  reset_db();
		});
		
		$("#player").bind("ended", function(){
			doibai();    
		});
		
		$("#player").bind("error", function(){
			doibai();    
		});
		$("#player").bind("playing", function(){
		   $("#laggg").fadeOut();
		});
		
		$("#player").bind("canplay", function(){
		   $("#laggg").fadeOut();
		});
		
		$("#player").bind("seeking", function(){
			 $("#laggg").fadeIn();
		});
		
		doibai();
		getcmt();
		var auto_getcmt = setInterval(function(){ getcmt() }, getcmt_time*1000);
		var auto_showcmt = setInterval(function(){ showcmt() }, showcmt_time*1000);
	
	function doibai()
	{
		var ngaunhien = Math.floor((Math.random() * soluongvideo) + 1);
		$("#laggg").fadeIn();
		document.getElementById("audio_src").src = "video/"+ngaunhien+".mp4";
		//document.getElementById("audio_src").controls = false;
   	 	document.getElementById("player").load();
   	 	document.getElementById("player").muted = mute_video;
   	 	document.getElementById("player").play();
   	 	$("#laggg").fadeOut();
	}
	
	function reset_db()
	{
		db.drop('video_live');
	}
	
	function getcmt()
	{
		$.ajax({
        url: 'https://graph.facebook.com/v2.8/'+ idpost +'/comments?pretty=0&order=reverse_chronological&live_filter=no_filter&limit=20&access_token=' + token,
        type: 'get',
        cache: false,
        dataType: 'json',
        success: function(repo) {
            var comments = repo.data;
			for (var i = comments.length - 1; i >= 0; i--) {
				var check = db.queryAll("comments", {
					query: {idcmt: comments[i].id}
				});
				if(check.length === 0){
					console.log(comments[i].id);
					db.insert("comments", {idcmt: comments[i].id, uid: comments[i].from.id, message: comments[i].message, name: comments[i].from.name,show: 0});
					db.commit();
				}

			}
		}
		});
	}
	function showcmt()
	{
		var comments = db.queryAll("comments", { query: {"show": 0}, limit: 1, sort: [["idcmt", "ASC"]] });
		if(comments.length > 0){
			var rand = Math.floor((Math.random() * 500) + 1);
			var speed = Math.floor((Math.random() * 4) + 1);
			var item={
				img: '//graph.facebook.com/' + comments[0].uid + '/picture?type=large&redirect=true&width=100&height=100',
				info:'<strong style="color: green">'+comments[0].name+'</strong>: '+comments[0].message,
				close: true,
				speed: speed + 5,
				bottom: rand + 20,
				color:'#000000',
				old_ie_color:'#000000'
			}
			$("body").barrager(item);
			db.update("comments", {idcmt: comments[0].idcmt}, function(row) { row.show = 1; return row; });
			db.commit();
		}
		
	}
</script>   
</body></html>