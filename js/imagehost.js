/*
 * imagehost.js - gHost
 * (C) Theodore Kluge 2015
 * villa7.github.io
*/

// var useContextMenu = true;
var winHeight = parseInt($(window).height());
var winWidth = parseInt($(window).width());
// var res;
var burgermenuactive = false, sortmenuactive = false, dropactive = false;
var numCanFit = 10;
// var loader;
var hasLoaded = false;
var prevSize = 0;
var needsReset = true;
var numFilesLoaded = 0;
// var minFile = null, maxFile = null;
// var lastLoaded = null;
// var loadedImages = [], loadedVideos = [];
var niceScroll;

var resize = _.debounce(function() {
    console.log("resized window");
    winHeight = parseInt($(window).height());
    winWidth = parseInt($(window).width());
    resizeAll(winWidth);
}, 200);
$(window).resize(resize);
Dropzone.options.dropzone = {
    paramName: "file",
    maxFilesize: 10000,
    previewsContainer: '.dropzone-previews',
    previewTemplate: $('#dz-template').html(),
    acceptedFiles: 'image/*',
    createImageThumbnails: false,
    dictDefaultMessage: '',
    clickable: $('#btn-upload')[0],
    init: function() {
        this.on('success', function(file, response) {
            console.log(response);
        }).on('addedfile', function(file) {
        	drop.open();
        }).on('complete', function(file) {
        	setTimeout(this.removeFile(file), 700);
        }).on('queuecomplete', function() {
        	setTimeout(function() {
        		drop.close();
        		needsReset = true;
        		clearContent();
        		loadContent();
        	}, 700);
        });
    }
};
function init() {
    resizeAll(winWidth);
    loadContent();
    niceScroll = $(".grid-box").niceScroll({ 
        scrollspeed: 80,
        mousescrollstep: 60,
        cursorborder: '0px',
        cursorcolor: "#444",
        horizrailenabled: false
    });
}
//cookies from stackoverflow
function getCookie(c_name) {
 var c_value = document.cookie;
 var c_start = c_value.indexOf(" " + c_name + "=");
 if (c_start == -1) {
     c_start = c_value.indexOf(c_name + "=");
 }
 if (c_start == -1) {
     c_value = null;
 } else {
     c_start = c_value.indexOf("=", c_start) + 1;
     var c_end = c_value.indexOf(";", c_start);
     if (c_end == -1) {
         c_end = c_value.length;
     }
     c_value = unescape(c_value.substring(c_start, c_end));
 }
 return c_value;
}
function setCookie(c_name, value, exdays) {
 var exdate = new Date();
 exdate.setDate(exdate.getDate() + exdays);
 var c_value = escape(value) + ((exdays == null) ? "" : "; expires=" + exdate.toUTCString());
 document.cookie = c_name + "=" + c_value;
}
function toggleBurger() {
    if (!burgermenuactive) {
        $('header .button-group-h').css({
            'right': '-1px'
            /*'opacity': 1,
            'width': '100%'*/
        }).addClass('h-v');
        $('.hamburger #hl-1').css({
            'transform': 'rotate(-45deg)',
            'top': '19px'
        });
        $('.hamburger #hl-2').css({
            'opacity': 0
        });
        $('.hamburger #hl-3').css({
            'transform': 'rotate(45deg)',
            'top': '19px'
        });
        $('.hamburger .btn').css({
            'background': '#80069D' 
        });
        burgermenuactive = true;
    } else {
        $('header .button-group-h').css({
            'right': '-300px'
           /* 'opacity': 0,
            'width': '0px'*/
        }).removeClass('h-v');
        $('.hamburger #hl-1').css({
            'transform': 'rotate(0deg)',
            'top': '12px'
        });
        $('.hamburger #hl-2').css({
            'opacity': 1
        });
        $('.hamburger #hl-3').css({
            'transform': 'rotate(0deg)',
            'top': '26px'
        });
        $('.hamburger .btn').css({
            'background': 'transparent' 
        });
        burgermenuactive = false;
    }
}
$('header .button-group-h .btn').on('click', function() {
    if (burgermenuactive) toggleBurger();
});
function resizeAll(width) {
    if (!burgermenuactive) {
        if (width >= 500) {
            $('header .button-group-h').css({
                'right': '-1px'
            });
        } else {
            $('header .button-group-h').css({
                'right': '-300px'
            });
        }
    }
    if (sortmenuactive) toggleSort();
    if (width > prevSize) {
        //loadContent(sortby, mode);
    }
    prevSize = width;
    /*if (winWidth > winHeight) {
        //d.info('wide');
        $('img.view, video.view').css({
            'height': '100%',
            'width': 'auto'
        });
    } else {
        //d.info('tall');
        $('img.view, video.view').css({
            'height': 'auto',
            'width': '100%'
        });
    }*/
}
function clearGrid() {
    $('.grid').html('<div class="grid-sizer"></div><div class="gutter-sizer"></div>');
}
function loadContent() { //try loading only as many as can fit on the screen
    numCanFit = Math.round(winHeight / 18);
    //d.info("loading: sortby " + sort + ", sfwmode " + sfw + " ("+numCanFit+")");

    if (!hasLoaded) {
        loader = new projectLoader();
        hasLoaded = true;
    } else {
        loader.start();   
    }

    if (!Backbone.History.started) {
        Backbone.history.start();
    }
    //$('.grid-box').focus();
    //d.info("using " + rows + " rows");
}
function clearContent() {
	var tblHeader = '<tr class="tbl-header">'+$('.grid table tr')[0].innerHTML+'</tr>';
	$('.grid table').empty().append(tblHeader);
}
var Data = Backbone.Model.extend({
  defaults: {
      name: '',
      hash: '',
      type: '',
      id: '',
      owner: '',
      lmdf: '',
      size: '',
      units: '',
      publicity: ''
  }
});
var DataList = Backbone.Collection.extend({
    model: Data,
	url: function() {
        var url = 'query.php?files&reset='+needsReset;
        if (needsReset) numFilesLoaded = 0;
        needsReset = false;
        return url;
    }
});
var AppView = Backbone.View.extend({
//set templates
  template_thumbnail: _.template($('#thumbnails').html()),
  initialize: function () {
  	$('.loader').fadeIn();

      this.collection.on('reset', this.render, this);
      c = this.collection;
      this.collection.fetch({
          success: function (model, response) {
          	console.info('Loaded');
              var files = response;
              var arr = [];
              for (var i=0; i<files.length; i++) {
                  obj = {
                      "name": files[i].file_name,
                      "hash": files[i].file_self,
                      "type": files[i].file_type.split('/')[1],
                      "id": files[i].PID,
                      "owner": files[i].owner,
                      "lmdf": files[i].last_modified,
                      "size": files[i].file_size,
                      "units": 'B'
                  };
                  if (files[i].file_size > 1000) {
                        if (files[i].file_size > 1000000) {
                            if (files[i].file_size > 1000000000) {
                                obj.units = 'GB';
                                obj.size = (obj.size / 1000000000).toFixed(2);
                            } else {
                                obj.units = 'MB';
                                obj.size = (obj.size / 1000000).toFixed(2);
                            }
                        } else {
                            obj.units = 'KB';
                            obj.size = (obj.size / 1000).toFixed(2);
                        }
                    }
                    obj.publicity = (files[i].is_shared == 1 ? 'public' : 'private');
                    obj.name = '<a href="'+obj.hash+'" target="_blank">'+obj.name+'</a>';
                    if (logged) obj.publicity = '<a href="#" onclick="toggleShared(\''+obj.hash+'\')">'+obj.publicity+'</a>';
                  numFilesLoaded++;
                  //obj.url = "http://placehold.it/3000x4000";
                  arr.push(obj);
              }
              $('.loader').fadeOut();
              c.reset(arr);
          },
          error: function () {
          }
      });
  },
  render: function () {
      this.collection.each(this.list, this);
      niceScroll.resize();
  },

  list: function (model) {
      var apitem = $(this.template_thumbnail({model: model}));
      if (numFilesLoaded > 0) $('.loadmore').css('display', 'inline');
      $('.grid table').append(apitem);
  }
});
var projectLoader = Backbone.Router.extend({
  routes: {
      '': 'start',
  },
  start: function () {
      new AppView({collection: new DataList()});
  }
});
function loadMore() { //called when the button at bottom is pressed
    loadContent();
}
function toggleShared(hash) {
    $.post('query.php',
        {
            toggle_shared: hash
        },
    function(result) {
        if (result == '') {
            console.log('toggled');
            if ($('#pub-'+hash+' a').html() == 'public') {
                $('#pub-'+hash+' a').html('private');
            } else {
                $('#pub-'+hash+' a').html('public');
            }
        } else {
            console.warn(result);
        }
    });
}
var drop = {
	toggle: function() {
		if (dropactive) {
			$('.dropzone-previews').css({
				'right': '-401px'
			});
			dropactive = false;
		} else {
			$('.dropzone-previews').css({
				'right': '0'
			});
			dropactive = true;
		}
	},
	open: function() {
		$('.dropzone-previews').css({
			'right': '0'
		});
		dropactive = true;
	},
	close: function() {
		$('.dropzone-previews').css({
			'right': '-401px'
		});
		dropactive = false;
	}
}
function confirmDelete(hash) {
	//psh confirming deletion
	$.post('query.php',
        {
            remove_image: hash
        },
    function(result) {
        if (result == '') {
            console.log('removed');
            $('#row-'+hash).remove();
        } else {
            console.warn(result);
        }
    });
}
$(document).on('click', '.tbl-col-del a', function() {
	confirmDelete($(this).attr('id').split('-')[1]);
});