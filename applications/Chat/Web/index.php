<html>
  <head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>PHP Chat-Room based on Websocket + Workerman</title>
  <script type="text/javascript">
  //WebSocket = null;
  </script>
  <link href="/workerman-chat-master/applications/Chat/Web/css/bootstrap.min.css" rel="stylesheet">
  <link href="/workerman-chat-master/applications/Chat/Web/css/style.css" rel="stylesheet">
  <style>

  .room_bg{
      background-repeat: no-repeat;
      background-position: top center;
      background-attachment: fixed;
      background-color: #FBFBFB;
      background-image: url('img/circuit.png');
      background-repeat: repeat-x ;
  }

  #fall_background {
      -webkit-transition: all 1s;
         -moz-transition: all 1s;
              transition: all 1s;
  }

  #fall_wrapper {
      -webkit-transition: all 1s;
         -moz-transition: all 1s;
              transition: all 1s;
      -webkit-perspective: 1300px;
      -moz-perspective: 1300px;
      perspective: 1300px;
  }

  #fall {
      -webkit-transition: all 1s ease-in;
      -moz-transition: all 1s ease-in;
      transition: all 1s ease-in;
      -webkit-transform-style: preserve-3d;
      -moz-transform-style: preserve-3d;
      transform-style: preserve-3d;
      -webkit-transform: translateZ(600px) rotateX(20deg);
      -moz-transform: translateZ(600px) rotateX(20deg);
      -ms-transform: translateZ(600px) rotateX(20deg);
      transform: translateZ(600px) rotateX(20deg);
  }

  .popup_visible #fall {
      -webkit-transform: translateZ(0px) rotateX(0deg);
      -moz-transform: translateZ(0px) rotateX(0deg);
      -ms-transform: translateZ(0px) rotateX(0deg);
      transform: translateZ(0px) rotateX(0deg);
  }

  </style>

  <script type="text/javascript" src="/workerman-chat-master/applications/Chat/Web/js/swfobject.js"></script>
  <script type="text/javascript" src="/workerman-chat-master/applications/Chat/Web/js/web_socket.js"></script>
  <script type="text/javascript" src="/workerman-chat-master/applications/Chat/Web/js/jquery.min.js"></script>
  <script type="text/javascript" src="/workerman-chat-master/applications/Chat/Web/js/json.js"></script>
  <script type="text/javascript" src="/workerman-chat-master/applications/Chat/Web/js/jquery.popupoverlay.js"></script>
  <script type="text/javascript">

    if (typeof console == "undefined") {
      this.console = { 
        log: function (msg) {} 
      };
    }

    WEB_SOCKET_SWF_LOCATION = "/workerman-chat-master/applications/Chat/Web/swf/WebSocketMain.swf";
    WEB_SOCKET_DEBUG = true;

    var ws, name, user_list={};

    function init(key , value) {

      // 创建websocket
    	ws = new WebSocket("ws://"+document.domain+":7272/?name="+key+"&password="+value);

      // 当socket连接打开
      ws.onopen = function() {

        // return ws.close(); 客户端关闭 socket

        // 隐藏悬浮框
        $('#close_fall')[0].click();

    	  ws.send(JSON.stringify({"type":"login","name":key}));

      };

      // 当有消息时根据消息类型显示不同信息
      ws.onmessage = function(e) {
    	  // console.log(e.data);
        var data = JSON.parse(e.data);
        switch(data['type']){

          // 展示用户列表
          case 'user_list':
            //{"type":"user_list","user_list":[{"uid":xxx,"name":"xxx"},{"uid":xxx,"name":"xxx"}]}
            flush_user_list(data);
            break;

          // 登录
          case 'login':
            //{"type":"login","uid":xxx,"name":"xxx","time":"xxx"}
            add_user_list(data['uid'], data['name']);
            say(data['uid'], 'all',  data['name']+' 加入了聊天室', data['time']);
            break;

          // 发言
          case 'say':
            //{"type":"say","from_uid":xxx,"to_uid":"all/uid","content":"xxx","time":"xxx"}
            say(data['from_uid'], data['to_uid'], data['content'], data['time']);
            break;

          // 用户退出 
          case 'logout':
            //{"type":"logout","uid":xxx,"time":"xxx"}
         	  say(data['uid'], 'all', user_list['_'+data['uid']]+' 退出了', data['time']);
         	  del_user_list(data['uid']);
        }
      };

      ws.onclose = function() {
    	  // console.log("服务端关闭了连接");
        alert('账密错误');
      };

      ws.onerror = function() {
    	  // console.log("出现错误");
        alert('服务器出现错误');
      };

    }

    // 提交对话
    function onSubmit() {
      var input = document.getElementById("textarea");
      ws.send(JSON.stringify({"type":"say","to_uid":"all","content":input.value}));
      input.value = "";
      input.focus();
    }

    // 将用户加如到当前用户列表
    function add_user_list(uid, name){
    	user_list['_'+uid] = name;
    	flush_user_list_window();
    }

    // 删除一个用户从用户列表
    function del_user_list(uid){
      delete user_list['_'+uid];
      flush_user_list_window();
    }

    // 刷新用户列表数据
    function flush_user_list(data){
      user_list = {};
      if('user_list' in data){
	      for(var p in data['user_list']){
	        user_list['_'+data['user_list'][p]['uid']] = data['user_list'][p]['name'];
	      }
      }
      flush_user_list_window();
    }

    // 刷新用户列表框
    function flush_user_list_window(){
      var userlist_window = document.getElementById("userlist");
      userlist_window.innerHTML = '<h4>Online User</h4><hr /><ul>';
      for(var p in user_list){
        userlist_window.innerHTML += '<li id="'+p+'">'+user_list[p]+'</li>';
      }
      userlist_window.innerHTML += '</ul>';
    }

    // 发言
    function say(from_uid, to_uid, content, time){
      var dialog_window = document.getElementById("dialog"); 
    	switch(to_uid){
    	  case 'all':
    		  if(user_list['_'+from_uid]){
    			  dialog_window.innerHTML +=  '<div class="speech_item"><img src="http://lorempixel.com/38/38/?'+from_uid+'" class="user_icon" /> '+user_list['_'+from_uid]+' <br> '+time+'<div style="clear:both;"></div><p class="triangle-isosceles top">'+content+'</p> </div>';
    			}
    			break;

    		// 私聊
    		default :
    			if(user_list['_'+from_uid]){
    			  dialog_window.innerHTML +=  '<div class="speech_item"><img src="http://lorempixel.com/38/38/?'+from_uid+'" class="user_icon" /> '+user_list['_'+from_uid]+' <br> '+time+'<div style="clear:both;"></div><p class="triangle-isosceles top">'+content+'</p> </div>';
   				}
          break;
    	}
      $('#dialog').scrollTop(1200);
    }

    $(document).ready(function(){

      $('#fall').popup();

      $('#open_fall')[0].click();

      $('#close_page').on('click',function(){
        location.href = "about:blank";
      });

      $('#sign_in').on('click',function(){

        var k = $('#user_name').val();
        var v = $('#pass_word').val();

        if(k == ''){
          $('#user_name').parent().addClass('has-error');
          $('#user_name').focus();
          return false;
        }

        if(v == ''){
          $('#pass_word').parent().addClass('has-error');
          $('#pass_word').focus();
          return false;
        }

        init(k , v);

      });
    });
  </script>
</head>
<body class="room_bg">

<!-- ************ 主界面 Start ************ -->
<div style="margin-top:10px;" class="container">

  <div class="row clearfix">

      <div class="col-md-1 column"></div>

      <div class="col-md-6 column">

         <div class="thumbnail">

           <!-- talk zone -->
           <div class="caption" id="dialog"></div>
           <!-- talk zone -->

         </div>

         <form onsubmit="onSubmit(); return false;">
           <!-- commit zone -->
           <textarea class="textarea thumbnail" id="textarea"></textarea>
           <div class="say-btn">
             <input id="send" type="submit" class="btn btn-success" value="发表" />
           </div>
         </form>

      </div>

      <div class="col-md-3 column">

        <div class="thumbnail">
          <!-- user_list -->
          <div class="caption" id="userlist"></div>
        </div>
      </div>

  </div>

</div>
<!-- ************ 主界面 End ************ -->

<!-- ************ 登录区 Start ************ -->
<div id="fall_wrapper" class="popup_wrapper" style="opacity: 0; visibility: hidden; position: fixed; overflow: auto; z-index: 100001; width: 100%; height: 100%; top: 0px; left: 0px; text-align: center; display: none;">
  <div id="fall" class="well popup_content popup_content_visible" style="max-width: 45em; opacity: 0; visibility: hidden; display: inline-block; outline: none; text-align: left; position: relative; vertical-align: middle;" aria-hidden="true" role="dialog" aria-labelledby="open_53632853" tabindex="-1">

      <div class="form-group">
        <div class="input-group">
          <div class="input-group-addon">@</div>
          <input id="user_name" class="form-control" type="text" placeholder="User Name">
        </div>
      </div>

      <div class="form-group">
        <label class="sr-only" for="exampleInputPassword2">Password</label>
        <input id="pass_word" type="password" class="form-control" id="exampleInputPassword2" placeholder="Password">
      </div>

      <button style="margin-left:10px;" id="close_page" class="pull-right btn btn-default">Close</button>
      <button type="button" id="sign_in" class="pull-right btn btn-success">Sign in</button>

      <button class="fall_close" id="close_fall" style="display:none;">CloseFall</button>
      <button id="open_fall" class="fall_open btn btn-default" style="display:none;">OpenFall</button>

  </div>
</div>
<!-- ************ 登录区 End ************ -->

</body>
</html>