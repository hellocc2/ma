<?php /* Smarty version 2.6.18, created on 2014-08-26 03:48:11
         compiled from time_lang.htm */ ?>
<form name="form" id="form" action="" target="mainFrame" method="post">
	<div id="date_lang"> 
		<input type="text" id="starttime" name="starttime" value="<?php echo $this->_tpl_vars['start_time']; ?>
" size=13  readonly="true" /> - 
		<input type="text" id="endtime" name="endtime" value="<?php echo $this->_tpl_vars['end_time']; ?>
" size=13  readonly="true" />
		<script>
		(function( $ ) {//onChange
			$( "#starttime" ).datepicker({ 
				dateFormat: 'yy-mm-dd', 
				onClose: function(){
					now = this.value;
				}
			});
			$( "#endtime" ).datepicker({ 
				dateFormat: 'yy-mm-dd', 
				onClose: function(){
					now = this.value;
				}
			});
		})( jQuery );
	</script>		
		<input type="submit" value="应用">
	</div>
</form> 

