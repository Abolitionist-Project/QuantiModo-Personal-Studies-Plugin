;(function($) {

$.fn.timer = function( useroptions ){ 
        var $this = $(this), opt,newVal, count = 0; 

        opt = $.extend( { 
            // Config 
            'timer' : 300, // 300 second default
            'width' : 24 ,
            'height' : 24 ,
            'fgColor' : "#ED7A53" ,
            'bgColor' : "#232323" 
            }, useroptions 
        ); 

        
        $this.knob({ 
            'min':0, 
            'max': opt.timer, 
            'readOnly': true, 
            'width': opt.width, 
            'height': opt.height, 
            'fgColor': opt.fgColor, 
            'bgColor': opt.bgColor,                 
            'displayInput' : false, 
            'dynamicDraw': false, 
            'ticks': 0, 
            'thickness': 0.1 
        }); 

        setInterval(function(){ 
            newVal = ++count; 
            $this.val(newVal).trigger('change'); 
        }, 1000); 
    };

// Necessary functions
function runnecessaryfunctions(){
  
  jQuery('.fitvids').fitVids();
  jQuery('.tip').tooltip();
  jQuery('.nav-tabs li:first a').tab('show');
  jQuery('.nav-tabs li a').click(function(){
    $(this).tab('show');
  });
  jQuery('.gallery').magnificPopup({
  delegate: 'a',
  type: 'image',
  tLoading: 'Loading image #%curr%...',
  mainClass: 'mfp-img-mobile',
  gallery: {
    enabled: true,
    navigateByImgClick: true,
    preload: [0,1] // Will preload 0 - before current, and 1 after the current image
  },
  image: {
    tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
    titleSrc: function(item) {
      return item.el.attr('title');
    }
  }
});

if ( typeof vc_js == 'function' ) { 
  //if($.isFunction(vc_js)){
    window.vc_js();
  }
}
//AJAX Comments
function ajaxsubmit_comments(){
  $('#question').each(function(){

   var $this=$(this);
  $('#submit').click(function(event){
    event.preventDefault();
    var value = '';
    $('#ajaxloader').removeClass('disabled');
    $('#question').css('opacity',0.2);
    $this.find('input[type="radio"]:checked').each(function(){
      value = $(this).val();
    });

    $this.find('input[type="checkbox"]:checked').each(function(){
      value= $(this).val()+','+value;
    });
    if($('#comment').hasClass('option_value'))
      $('#comment.option_value').val(value);

    $('#commentform').submit();
  });
    
  var commentform=$('#commentform'); // find the comment form
  var statusdiv=$('#comment-status'); // define the infopanel
  var qid = statusdiv.attr('data-quesid');
  
  commentform.submit(function(){

    var formdata=commentform.serialize();

    statusdiv.html('<p>'+qm_study_module_strings.processing+'</p>');

    var formurl=commentform.attr('action');

    $.ajax({
      type: 'post',
      url: formurl,
      data: formdata,
      error: function(XMLHttpRequest, textStatus, errorThrown){
        $('#ajaxloader').addClass('disabled');
        $('#question').css('opacity',1);
        statusdiv.html('<p class="wdpajax-error">'+qm_study_module_strings.too_fast_answer+'</p>');
      },
      success: function(data, textStatus){
        $('#question').css('opacity',1);
        $('#ajaxloader').addClass('disabled');
        if(data=="success"){
          statusdiv.html('<p class="ajax-success" >'+qm_study_module_strings.answer_saved+'</p>');
          $('#ques'+qid).addClass('done');
        }
        else
          statusdiv.html('<p class="ajax-error" >'+qm_study_module_strings.saving_answer+'</p>');
          //commentform.find('textarea[name=comment]').val('');
        }
    });
    return false;
    });
  }); 
} // END Function



jQuery(document).ready( function($) {
	
	$("#average .dial").knob({
	  	'readOnly': true, 
	    'width': 120, 
	    'height': 120, 
	    'fgColor': qm_study_module_strings.theme_color, 
	    'bgColor': '#f6f6f6',   
	    'thickness': 0.1
	});
	$("#pass .dial").knob({
	  	'readOnly': true, 
	    'width': 120, 
	    'height': 120, 
	    'fgColor': qm_study_module_strings.theme_color, 
	    'bgColor': '#f6f6f6',   
	    'thickness': 0.1
	});
	$("#badge .dial").knob({
	  	'readOnly': true, 
	    'width': 120, 
	    'height': 120, 
	    'fgColor': qm_study_module_strings.theme_color, 
	    'bgColor': '#f6f6f6',   
	    'thickness': 0.1
	});

	$(".study_quiz .dial").knob({
	  	'readOnly': true, 
	    'width': 120, 
	    'height': 120, 
	    'fgColor': qm_study_module_strings.theme_color, 
	    'bgColor': '#f6f6f6',   
	    'thickness': 0.1 
	});
  //RESET Ajx
$( 'body' ).delegate( '.remove_user_study','click',function(event){
      event.preventDefault();
      var study_id=$(this).attr('data-study');
      var user_id=$(this).attr('data-user');
      $(this).addClass('animated spin');
      var $this = $(this);
      $.confirm({
          text: qm_study_module_strings.remove_user_text,
          confirm: function() {
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'remove_user_study', 
                            security: $('#security').val(),
                            id: study_id,
                            user: user_id
                          },
                    cache: false,
                    success: function (html) {
                        $(this).removeClass('animated');
                        $(this).removeClass('spin');
                        runnecessaryfunctions();
                        $('#message').html(html);
                        $('#s'+user_id).fadeOut('fast');
                    }
            });
          },
          cancel: function() {
              $this.removeClass('animated');
              $this.removeClass('spin');
          },
          confirmButton: qm_study_module_strings.remove_user_button,
          cancelButton: qm_study_module_strings.cancel
      });
	});

$( 'body' ).delegate( '.reset_study_user','click',function(event){
      event.preventDefault();
      var study_id=$(this).attr('data-study');
      var user_id=$(this).attr('data-user');
      $(this).addClass('animated spin');
      var $this = $(this);
      $.confirm({
        text: qm_study_module_strings.reset_user_text,
          confirm: function() {
          $.ajax({
                  type: "POST",
                  url: ajaxurl,
                  data: { action: 'reset_study_user', 
                          security: $('#security').val(),
                          id: study_id,
                          user: user_id
                        },
                  cache: false,
                  success: function (html) {
                      $this.removeClass('animated');
                      $this.removeClass('spin');
                      $('#message').html(html);
                  }
          });
         }, 
         cancel: function() {
              $this.removeClass('animated');
              $this.removeClass('spin');
          },
          confirmButton: qm_study_module_strings.reset_user_button,
          cancelButton: qm_study_module_strings.cancel
        });
	});

  
$( 'body' ).delegate( '.study_stats_user', 'click', function(event){
      event.preventDefault();
      var $this=$(this);
      var study_id=$this.attr('data-study');
      var user_id=$this.attr('data-user');
      
      if($this.hasClass('already')){
      	$('#s'+user_id).find('.study_stats_user').fadeIn('fast');
      }else{
      	  $this.addClass('animated spin');		
	      $.ajax({
	              type: "POST",
	              url: ajaxurl,
	              data: { action: 'study_stats_user', 
	                      security: $('#security').val(),
	                      id: study_id,
	                      user: user_id
	                    },
	              cache: false,
	              success: function (html) {
	                  $this.removeClass('animated');
	                  $this.removeClass('spin');
	                  $this.addClass('already');
	                  $('#s'+user_id).append(html);
	                  $(".dial").knob({
	                  	'readOnly': true, 
			            'width': 160, 
			            'height': 160, 
			            'fgColor': qm_study_module_strings.theme_color, 
			            'bgColor': '#f6f6f6',   
			            'thickness': 0.3 
	                  });
	              }
	      });
  		}
	});


  $('#calculate_avg_study').click(function(event){
      event.preventDefault();
      var study_id=$(this).attr('data-studyid');
      $(this).addClass('animated spin');

      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'calculate_stats_study', 
                      security: $('#security').val(),
                      id: study_id
                    },
              cache: false,
              success: function (html) {
                  $(this).removeClass('animated');
                  $(this).removeClass('spin');
                  $('#message').html(html);
                   setTimeout(function(){location.reload();}, 3000);
              }
      });

  });

  $('.reset_quiz_user').click(function(event){
      event.preventDefault();
      var study_id=$(this).attr('data-quiz');
      var user_id=$(this).attr('data-user');
      $(this).addClass('animated spin');
      var $this = $(this);
      $.confirm({
          text: qm_study_module_strings.quiz_rest,
          confirm: function() {

      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'reset_quiz', 
                      security: $('#qsecurity').val(),
                      id: study_id,
                      user: user_id
                    },
              cache: false,
              success: function (html) {
                  $(this).removeClass('animated');
                  $(this).removeClass('spin');
                  $('#message').html(html);
                  $('#qs'+user_id).fadeOut('fast');
              }
      });
      }, 
       cancel: function() {
            $this.removeClass('animated');
            $this.removeClass('spin');
        },
        confirmButton: qm_study_module_strings.quiz_rest_button,
        cancelButton: qm_study_module_strings.cancel
      });
  });

  $('.evaluate_quiz_user').click(function(event){
      event.preventDefault();
      var quiz_id=$(this).attr('data-quiz');
      var user_id=$(this).attr('data-user');
      $(this).addClass('animated spin');

      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'evaluate_quiz', 
                      security: $('#qsecurity').val(),
                      id: quiz_id,
                      user: user_id
                    },
              cache: false,
              success: function (html) {
                  $(this).removeClass('animated');
                  $(this).removeClass('spin');
                  $('.quiz_students').html(html);
                  calculate_total_marks();
              }
      });
  });


 $('.evaluate_study_user').click(function(event){
      event.preventDefault();
      var study_id=$(this).attr('data-study');
      var user_id=$(this).attr('data-user');
      $(this).addClass('animated spin');

      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'evaluate_study', 
                      security: $('#security').val(),
                      id: study_id,
                      user: user_id
                    },
              cache: false,
              success: function (html) {
                  $(this).removeClass('animated');
                  $(this).removeClass('spin');
                  $('.study_students').html(html);
                  calculate_total_marks();
              }
      });
  });

$( 'body' ).delegate( '.reset_answer', 'click', function(event){
       event.preventDefault();
      var ques_id=$('#comment-status').attr('data-quesid');
      var $this = $(this);
      var defaulttxt = $this.html();
      $this.prepend('<i class="icon-sun-stroke animated spin"></i>');
      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'reset_question_answer', 
                      security: $this.attr('data-security'),
                      ques_id: ques_id,
                    },
              cache: false,
              success: function (html) {
                  $this.find('i').remove();
                   $this.html(html);
                   setTimeout(function(){$this.html(defaulttxt);}, 2000);
              }
      });
});

$( 'body' ).delegate( '#study_complete', 'click', function(event){
      event.preventDefault();
      var $this=$(this);
      var user_id=$this.attr('data-user');
      var study = $this.attr('data-study');
      var marks = parseInt($('#study_marks_field').val());
      if(marks <= 0){
        alert('Enter Marks for User');
        return;
      }

      $this.prepend('<i class="icon-sun-stroke animated spin"></i>');
      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'complete_study_marks', 
                      study: study,
                      user: user_id,
                      marks:marks
                    },
              cache: false,
              success: function (html) {
                  $this.find('i').remove();
                  $this.html(html);
              }
      });
});

  // Registeration BuddyPress
  $('.register-section h4').click(function(){
      $(this).toggleClass('show');
      $(this).parent().find('.editfield').toggle('fast');
  });

});

$( 'body' ).delegate( '.hide_parent', 'click', function(event){
	$(this).parent().fadeOut('fast');
});


$( 'body' ).delegate( '.give_marks', 'click', function(event){
	    event.preventDefault();
	    var $this=$(this);
	    var ansid=$this.attr('data-ans-id');
	    var aval = $('#'+ansid).val();
	    $this.prepend('<i class="icon-sun-stroke animated spin"></i>');
	    $.ajax({
	            type: "POST",
	            url: ajaxurl,
	            data: { action: 'give_marks', 
	                    aid: ansid,
	                    aval: aval
	                  },
	            cache: false,
	            success: function (html) {
	                $this.find('i').remove();
	                $this.html(qm_study_module_strings.marks_saved);
	            }
	    });
});

$( 'body' ).delegate( '#mark_complete', 'click', function(event){
    event.preventDefault();
    var $this=$(this);
    var quiz_id=$this.attr('data-quiz');
    var user_id = $this.attr('data-user');
    var marks = parseInt($('#total_marks strong > span').text());
    $this.prepend('<i class="icon-sun-stroke animated spin"></i>');
    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'save_quiz_marks', 
                    quiz_id: quiz_id,
                    user_id: user_id,
                    marks: marks,
                  },
            cache: false,
            success: function (html) {
                $this.find('i').remove();
                $this.html(qm_study_module_strings.quiz_marks_saved);
            }
    });
});

function calculate_total_marks(){
  $('.question_marks').blur(function(){
      var marks=parseInt(0);
      var $this = $('#total_marks strong > span');
      $('.question_marks').each(function(){
          if($(this).val())
            marks = marks + parseInt($(this).val());
        });
      $this.html(marks);
  });
}


$( 'body' ).delegate( '.submit_quiz', 'click', function(event){
    event.preventDefault();
    if($(this).hasClass('disabled')){
      return false;
    }
     
    var $this = $(this);
    var quiz_id=$(this).attr('data-quiz');
    $this.prepend('<i class="icon-sun-stroke animated spin"></i>');
    $('#question').addClass('quiz_submitted_fade');
    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'submit_quiz', 
                    start_quiz: $('#start_quiz').val(),
                    id: quiz_id
                  },
            cache: false,
            success: function (html) {
                $('#ajaxloader').removeClass('disabled');
                $('#question').css('opacity',0.2);
                $this.find('i').remove();
                location.reload();
            }
    });
});

// QUIZ RELATED FUCNTIONS
// START QUIZ AJAX
jQuery(document).ready( function($) {
	$('.begin_quiz').click(function(event){
	    event.preventDefault();
	    var $this = $(this);
	    var quiz_id=$(this).attr('data-quiz');
	    $this.prepend('<i class="icon-sun-stroke animated spin"></i>');
	    $.ajax({
	            type: "POST",
	            url: ajaxurl,
	            data: { action: 'begin_quiz', 
	                    start_quiz: $('#start_quiz').val(),
	                    id: quiz_id
	                  },
	            cache: false,
	            success: function (html) {
	                $this.find('i').remove();
	                $('.content').fadeOut("fast");
	                $('.content').html(html);
	                $('.content').fadeIn("fast");
	                ajaxsubmit_comments();
	                var ques=$($.parseHTML(html)).filter("#question");
	                var q='#ques'+ques.attr('data-ques');

	                $('.quiz_timeline').find('.active').removeClass('active');
	                $(q).addClass('active');
                  $('#question').trigger('question_loaded');
	                if(ques != 'undefined'){
	                  $('.quiz_timer').trigger('activate');
	                }

                $('.begin_quiz').each(function(){
                    $(this).removeClass('begin_quiz');
                    $(this).addClass('submit_quiz');
                    $(this).text(qm_study_module_strings.submit_quiz);
                });
            }
        });
	});
});






$( 'body' ).delegate( '.quiz_question', 'click', function(event){
    event.preventDefault();
    var $this = $(this);
    var quiz_id=$(this).attr('data-quiz');
    var ques_id=$(this).attr('data-qid');
    $this.prepend('<i class="icon-sun-stroke animated spin"></i>');
    $('#ajaxloader').removeClass('disabled');
    $('#question').css('opacity',0.2);
    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'quiz_question', 
                    start_quiz: $('#start_quiz').val(),
                    quiz_id: quiz_id,
                    ques_id: ques_id
                  },
            cache: false,
            success: function (html) {
                $this.find('i').remove();
                $('.content').html(html);
                $('#ajaxloader').addClass('disabled');
                $('#question').css('opacity',1);
                ajaxsubmit_comments();
                var ques=$($.parseHTML(html)).filter("#question");
                var q='#ques'+ques.attr('data-ques');
                $('.quiz_timeline').find('.active').removeClass('active');
                $(q).addClass('active');
                $('#question').trigger('question_loaded');
                if(ques != 'undefined')
                  $('.quiz_timer').trigger('activate');
            }
      });
});

$( 'body' ).delegate( '#question', 'question_loaded',function(){
  
  jQuery('.question_options.sort').each(function(){

    var defaultanswer='1';
    var lastindex = $('ul.question_options li').size();
    if(lastindex>1)
    for(var i=2;i<=lastindex;i++){
      defaultanswer = defaultanswer+','+i;
    }
    $('#comment').val(defaultanswer);
    $('#comment').trigger('change');
    jQuery('.question_options.sort').sortable({
      revert: true,
      cursor: 'move',
      refreshPositions: true, 
      opacity: 0.6,
      scroll:true,
      containment: 'parent',
      placeholder: 'placeholder',
      tolerance: 'pointer',
      update: function( event, ui ) {
          var order = $('.question_options.sort').sortable('toArray').toString();
          $('#comment').val(order);
          $('#comment').trigger('change');
      }
    }).disableSelection();
  });
});



jQuery(document).ready( function($) {
  $('.quiz_timer').each(function(){
      var qtime = parseInt($(this).attr('data-time'));
      var $timer =$(this).find('.timer');
      $timer.knob({
        'readonly':true,
        'max': qtime,
        'width' : 200 ,
        'height' : 200 ,
        'fgColor' : qm_study_module_strings.theme_color ,
        'bgColor' : "#232b2d",
        'thickness': 0.2 ,
        'readonly':true 
      });
  });

  $('.quiz_timer').one('activate',function(){
    var qtime = parseInt($(this).attr('data-time'));

    var $timer =$(this).find('.timer');
    var $this=$(this);
    
    $timer.timer({
      'timer': qtime,
      'width' : 200 ,
      'height' : 200 ,
      'fgColor' : qm_study_module_strings.theme_color ,
      'bgColor' : "#232b2d" 
    });

    var $timer =$(this).find('.timer');

    $timer.on('change',function(){
        var countdown= $this.find('.countdown');
        var val = parseInt($timer.attr('data-timer'));
        if(val > 0){
          val--;
          $timer.attr('data-timer',val);
          var $text='';
          if(val > 60){
            $text = Math.floor(val/60) + ':' + ((parseInt(val%60) < 10)?'0'+parseInt(val%60):parseInt(val%60)) + '';
          }else{
            $text = '00:'+ ((val < 10)?'0'+val:val);
          }

          countdown.html($text);
        }else{
            countdown.html('Timeout');
            $('.submit_quiz').trigger('click');
            $('.quiz_timer').trigger('end');
        }  
    });
    
  });

  $('.quiz_timer').one('deactivate',function(){
    var qtime = parseInt($(this).attr('data-time'));
    var $timer =$(this).find('.timer');
    var $this=$(this);
    
    $timer.knob({
        'readonly':true,
        'max': qtime,
        'width' : 200 ,
        'height' : 200 ,
        'fgColor' : qm_study_module_strings.theme_color ,
        'bgColor' : "#232b2d",
        'thickness': 0.2 ,
        'readonly':true 
      });
    event.stopPropagation();
  });

  $('.quiz_timer').one('end',function(event){
    var qtime = parseInt($(this).attr('data-time'));
    var $timer =$(this).find('.timer');
    var $this=$(this);
    
    $timer.knob({
        'readonly':true,
        'max': qtime,
        'width' : 200 ,
        'height' : 200 ,
        'fgColor' : qm_study_module_strings.theme_color ,
        'bgColor' : "#232b2d",
        'thickness': 0.2 ,
        'readonly':true 
      });
    event.stopPropagation();
  });

jQuery('.question_options.sort').each(function(){
    var defaultanswer='1';
    var lastindex = $('ul.question_options li').size();
    if(lastindex>1)
    for(var i=2;i<=lastindex;i++){
      defaultanswer = defaultanswer+','+i;
    }
    $('#comment').val(defaultanswer);
    $('#comment').trigger('change');
    jQuery('.question_options.sort').sortable({
      revert: true,
      cursor: 'move',
      refreshPositions: true, 
      opacity: 0.6,
      scroll:true,
      containment: 'parent',
      placeholder: 'placeholder',
      tolerance: 'pointer',
      update: function( event, ui ) {
          var order = $('.question_options.sort').sortable('toArray').toString();
          $('#comment').val(order);
          $('#comment').trigger('change');
      }
    }).disableSelection();
  });
}); 

$( 'body' ).delegate( '.expand_message', 'click', function(event){
  event.preventDefault();
  $('.bulk_message').toggle('slow');
});

$( 'body' ).delegate( '.expand_add_students', 'click', function(event){
  event.preventDefault();
  $('.bulk_add_students').toggle('slow');
});

$( 'body' ).delegate( '.expand_assign_students', 'click', function(event){
  event.preventDefault();
  $('.bulk_assign_students').toggle('slow');
});

$( 'body' ).delegate( '#send_study_message', 'click', function(event){
  event.preventDefault();
  var members=[];

  var $this = $(this);
  var defaultxt=$this.html();
  $this.html('<i class="icon-sun-stroke animated spin"></i> '+qm_study_module_strings.sending_messages);
  var i=0;
  $('.member').each(function(){
    if($(this).is(':checked')){
      members[i]=$(this).val();
      i++;
    }
  });
  $.ajax({
        type: "POST",
        url: ajaxurl,
        data: { action: 'send_bulk_message', 
                security: $('#buk_action').val(),
                study:$this.attr('data-study'),
                sender: $('#sender').val(),
                members: JSON.stringify(members),
                subject: $('#bulk_subject').val(),
                message: $('#bulk_message').val(),
              },
        cache: false,
        success: function (html) {
            $('#send_study_message').html(html);
            setTimeout(function(){$this.html(defaultxt);}, 5000);
        }
    });    
});

$( 'body' ).delegate( '#add_student_to_study', 'click', function(event){
  event.preventDefault();
  var $this = $(this);
  var defaultxt=$this.html();
  var students = $('#student_usernames').val();

  if(students.length <= 0){ 
    $('#add_student_to_study').html(qm_study_module_strings.unable_add_students);
    setTimeout(function(){$this.html(defaultxt);}, 2000);
    return;
  }

  $this.html('<i class="icon-sun-stroke animated spin"></i>'+qm_study_module_strings.adding_students);
  var i=0;
  $.ajax({
        type: "POST",
        url: ajaxurl,
        data: { action: 'add_bulk_students', 
                security: $('#buk_action').val(),
                study:$this.attr('data-study'),
                members: students,
              },
        cache: false,
        success: function (html) {
          console.log(html);
          if(html.length && html !== '0'){
            $('#add_student_to_study').html(qm_study_module_strings.successfuly_added_students);
            $('ul.study_students').append(html);
          }else{
            $('#add_student_to_study').html(qm_study_module_strings.unable_add_students);
          }
            
            setTimeout(function(){$this.html(defaultxt);}, 3000);
        }
    });    
});


$( 'body' ).delegate( '#assign_study_badge_certificate', 'click', function(event){
  event.preventDefault();
  var members=[];

  var $this = $(this);
  var defaultxt=$this.html();
  $this.html('<i class="icon-sun-stroke animated spin"></i> '+qm_study_module_strings.processing);
  var i=0;
  $('.member').each(function(){
    if($(this).is(':checked')){
      members[i]=$(this).val();
      i++;
    }
  });

  $.ajax({
        type: "POST",
        url: ajaxurl,
        data: { action: 'assign_badge_certificates', 
                security: $('#buk_action').val(),
                study: $this.attr('data-study'),
                members: JSON.stringify(members),
                assign_action: $('#assign_action').val(),
              },
        cache: false,
        success: function (html) {
            $this.html(html);
            setTimeout(function(){$this.html(defaultxt);}, 5000);
        }
    });    
});


// Study Unit Traverse
$( 'body' ).delegate( '.unit', 'click', function(event){
    event.preventDefault();
    if($(this).hasClass('disabled')){
      return false;
    }
    
    var $this = $(this);
    var unit_id=$(this).attr('data-unit');
    $this.prepend('<i class="icon-sun-stroke animated spin"></i>');
    
    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'unit_traverse', 
                    security: $('#hash').val(),
                    study_id: $('#study_id').val(),
                    id: unit_id
                  },
            cache: false,
            success: function (html) {
                 $('body,html').animate({
                    scrollTop: 0
                  }, 1200);
                $this.find('i').remove();

                $('.unit_content').fadeOut("fast");
                $('.unit_content').html(html);
                $('.unit_content').fadeIn("fast");
                $('.unit_content').trigger('unit_traverse');

                var unit=$($.parseHTML(html)).filter("#unit");
                var u='#unit'+unit.attr('data-unit');
                $('.study_timeline').find('.active').removeClass('active');
                $(u).addClass('active');

                $('audio,video').mediaelementplayer({
                    success: function(player, node) { 
                      $('#mark-complete').trigger('media_loaded');
                      $('.mejs-container').each(function(){
                        $(this).addClass('mejs-mejskin');
                      });
                    }
                });
                $("audio,video").bind("ended", function() {
                    $('#mark-complete').trigger('media_complete');
                });
                runnecessaryfunctions();

                if(typeof unit != 'undefined')
                  $('.unit_timer').trigger('activate');
            }
    });
});

$( 'body' ).delegate( '#mark-complete', 'media_loaded', function(event){
  event.preventDefault();
  if($(this).hasClass('tip')){
      $(this).addClass('disabled');
  }
});

$( 'body' ).delegate( '#mark-complete', 'media_complete', function(event){
  event.preventDefault();
  if($(this).hasClass('tip')){
    $(this).removeClass('disabled');
    $(this).removeClass('tip');
    $(this).tooltip('destroy');
    jQuery('.tip').tooltip();
  }  
});


$( 'body' ).delegate( '#mark-complete', 'click', function(event){
    event.preventDefault();
    if($(this).hasClass('disabled')){
      return false;
    }

    var $this = $(this);
    var unit_id=$(this).attr('data-unit');
    $this.prepend('<i class="icon-sun-stroke animated spin"></i>');
    $('body').find('.study_progressbar').removeClass('increment_complete');
    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'complete_unit', 
                    security: $('#hash').val(),
                    study_id: $('#study_id').val(),
                    id: unit_id
                  },
            cache: false,
            success: function (html) {
                $this.find('i').remove();
                $this.html('<i class="icon-check"></i>');
                $('.study_timeline').find('.active').addClass('done');
                $('body').find('.study_progressbar').trigger('increment');
                $('#mark-complete').addClass('disabled');
                
                if(html.length > 0){
                    $('#next_unit').removeClass('hide');
                    $('#next_unit').attr('data-unit',html);  
                    $('#unit'+html).find('a').addClass('unit');
                    $('#unit'+html).find('a').attr('data-unit',html);
                }
                if(typeof unit != 'undefined')
                  $('.unit_timer').trigger('finish');
            }
    });
});

$('.study_progressbar').on('increment',function(event){

  if($(this).hasClass('increment_complete')){
    event.stopPropagation();
    return false;
  }else{
    console.log('captured');
    var iunit = parseInt($(this).attr('data-increase-unit'));
    var per = parseInt($(this).attr('data-value'));
    newper = iunit + per;
    $(this).find('.bar').css('width',newper+'%');
    $(this).find('.bar span').html(newper + '%');
    $(this).addClass('increment_complete');
    $(this).attr('data-value',newper);
  }
  event.stopPropagation();
  return false;
  
});

jQuery(document).ready(function($){
	$('.showhide_indetails').click(function(event){
		event.preventDefault();
		$(this).find('i').toggleClass('icon-minus');
		$(this).parent().find('.in_details').toggle();
	});

$('.ajax-certificate').each(function(){
    $(this).magnificPopup({
          type: 'ajax',
          fixedContentPos: true,
          alignTop:true,
          preloader: false,
          midClick: true,
          removalDelay: 300,
          showCloseBtn:false,
          mainClass: 'mfp-with-zoom',
          callbacks: {
             parseAjax: function( mfpResponse ) {
              mfpResponse.data = $(mfpResponse.data).find('#certificate');
            },
            ajaxContentAdded: function() {
              html2canvas($('#certificate'), {
                  onrendered: function(canvas) {
                      var data = canvas.toDataURL();
                      $('#certificate .certificate_content').html('<img src="'+data+'" />');
                      $('#certificate').trigger('generate_certificate');
                  }
              });
            }
          }
      });
});

$( 'body' ).delegate( '.print_unit', 'click', function(event){
    $('.unit_content').print();
});

$( 'body' ).delegate( '.printthis', 'click', function(event){
    $(this).parent().print();
});

$( 'body' ).delegate( '#certificate', 'generate_certificate', function(event){
    $(this).addClass('certificate_generated');
});

$( 'body' ).delegate( '.certificate_print', 'click', function(event){
    event.preventDefault();
    $(this).parent().parent().print();
});

$('.widget_carousel').flexslider({
  animation: "slide",
  controlNav: false,
  directionNav: true,
  animationLoop: true,
  slideshow: false,
  prevText: "<i class='icon-arrow-1-left'></i>",
  nextText: "<i class='icon-arrow-1-right'></i>",
});

  /*=== Quick tags ===*/
  $( 'body' ).delegate( '.unit-page-links a', 'click', function(event){
        if($('body').hasClass('single-unit'))
          return;

        event.preventDefault();
        
        var $this=$(this);
        $this.prepend('<i class="icon-sun-stroke animated spin"></i>');
        $( ".main_unit_content" ).load( $this.attr('href') +" .single_unit_content" );
        runnecessaryfunctions();
        $('body').trigger('unit_loaded');
        $this.find('i').remove();
        $( ".main_unit_content" ).trigger('unit_reload');
    });

  });	
})(jQuery);