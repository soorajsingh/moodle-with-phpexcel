<?php 
require('../../../config.php');
require_login();
global $CFG; $PAGE; $DB; 
$id= optional_param('id', 0, PARAM_INT);
$locid=optional_param('locid',0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$keysearch=optional_param('search1', null, PARAM_ALPHANUM);
$paging=10;	
$from = ($page)* $paging; 
$pagetitle = get_string('create_group','block_adminlink');
if($id){
 $pagetitle = get_string('update_group','block_adminlink');
}

$header =  get_string('group','block_adminlink');
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/blocks/adminlink/locadmin/create_group.php',array('id'=>$id,'locid'=>$locid)));

$PAGE->set_pagelayout('standard');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($header);
if($locid==0)
{
    $locadmin=$DB->get_record_select("role_assignments",'userid=? and contextid=1 and roleid=1 and locid!=1',array($USER->id));
            	$locid=$locadmin->locid;
}
/////create form
require_once($CFG->libdir.'/formslib.php');
echo $OUTPUT->header(); 
	if(has_capability('block/adminlink:managegroup',context_system::instance()))
	{
$message='';
//$DB->set_debug(true);
class create_group_form extends moodleform {
    
    public function definition() {
        global $CFG,$id,$DB,$locid,$pagetitle;
		$groupid=null;
		$groupname=null;
		$groupdept=null;
	$custom=null;
	$change_id=null;	
	    if($id)
		{
			$rec1=$DB->get_record('hierarchy_group',array('id'=>$id));  
			$groupid=$rec1->idnum;
			$groupname=$rec1->name;
			$groupdept=$rec1->department;
		    //echo $groupid;
		    //echo $groupname;
		}
		
		
		$mform = $this->_form;
		$mform->addElement('header', 'nameforyourheaderelement',$pagetitle);
		
		$mform->addElement('hidden', 'change_id' ,'change', 'array(id="change_id")');
        $mform->setType('change_id', PARAM_NOTAGS);
		$mform->setDefault('change_id',$change_id);
		
		$mform->addElement('hidden', 'custom' ,'custom', 'array(id="custom")');
        $mform->setType('custom', PARAM_NOTAGS);
		$mform->setDefault('custom',$custom);
		
		$mform->addElement('text', 'name', get_string('group_name','block_adminlink'),array('onchange'=>'changeanything()','placeholder'=>get_string('enter_groupname','block_adminlink'),'maxlength'=>300,'size'=>50));
        $mform->setType('name', PARAM_NOTAGS);               
		$mform->setDefault('name',$groupname);
		$mform->addRule('name', get_string('required_field','block_adminlink'), 'required' ,null ,'client');
		//$mform->addRule('name','First two characters must be alphabet and after that you can use alphanumeric characters and spaces only', 'regex','/^[a-zA-Z]{2}+[0-9a-zA-Z ]*$/','client');
	  	$mform->addRule('name',get_string('group_name_alert1','block_adminlink'), 'minlength',2, 'client');                                                                    
        //$mform->addRule('name', get_string('group_name_alert2','block_adminlink'), 'maxlength', 25, 'client');
	   
	    
	    
	    
	    $mform->addElement('text', 'idnum', get_string('group_id','block_adminlink'),array('placeholder'=>get_string('enter_groupid','block_adminlink'),'onchange'=>'changeanything()','maxlength'=>300,'size'=>50));
	  
        $mform->setType('idnum', PARAM_NOTAGS);                   
		$mform->setDefault('idnum',$groupid);
		$mform->addRule('idnum', get_string('required_field','block_adminlink'), 'required' ,null ,'client');
		 $mform->addRule('idnum', get_string('group_idnum_alert1','block_adminlink'), 'alphanumeric', null, 'client');
		$mform->addRule('idnum',get_string('group_idnum_alert2','block_adminlink'), 'minlength',4, 'client'); 
		//$mform->addRule('idnum','Atleast one alphabet and one numeric value must be there', 'regex','%([a-zA-Z]{1}+[0-9]{1})|([0-9]{1}+[a-zA-Z]{1})%','client');
		
		
	 $processnew=$DB->get_records_sql("SELECT * FROM mdl_hierarchy_department order by name");
            	$data=array();
        		$data['']=get_string('select_dept','block_adminlink');	
        		foreach($processnew as $pro)
            		{     
            		
            			$data[$pro->id]=$pro->name;
                    } 
        		        
        		$mform->addElement('select', 'department', get_string('select_dept','block_adminlink'),$data,array('onchange'=>'changeanything()'));
        	    $mform->addRule('department', get_string('required_field','block_adminlink'), 'required' ,null ,'client');
	    	   	$mform->setDefault('department',$groupdept);
		

        
		
		//normally you use add_action_buttons instead of this code
		$buttonarray=array();
		$buttonname='';
		if($id==0)
		{
			$buttonname=get_string('create','block_adminlink');	
		}
		else
		{
			$buttonname=get_string('update','block_adminlink');	   
		}
		$buttonarray[] = &$mform->createElement('submit', 'submitbutton', $buttonname);

		$buttonarray[] = &$mform->createElement('cancel',get_string('cancel','block_adminlink'));
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
	}
		
        //Custom validation should be added here
   function validation($data, $files) {
       global $DB,$id;
        $idnum=$data['idnum'];
        $error=array();
            if($id==0)
        	{
               
            $sql= $DB->get_records('hierarchy_group', array('idnum' => $idnum,'active_status'=>'0'));
            $count=count($sql);
        	
                if($count)
                  {
                   $error['idnum']=get_string('group_error','block_adminlink');
                   return $error;
                  }
           
        	}
		 else
		{
		
         $sql= $DB->get_records_select('hierarchy_group','idnum=? and active_status=? and id != ?', array($idnum,0, $id));
         $count=count($sql);
   
            if($count)
             {
              $error['idnum']=get_string('group_error','block_adminlink');
               return $error;
             }
         
	    }
	   
	   
	   
 }
        
        
}


$from_url=new moodle_url('/blocks/adminlink/locadmin/create_group.php',array('id'=>$id,'locid'=>$locid));
	$message=get_string('create_group','block_adminlink');


if($id){

$from_url=new moodle_url('/blocks/adminlink/locadmin/create_group.php',array('id'=>$id,'locid'=>$locid));
	$message=get_string('update_group','block_adminlink');
	
}

$mform = new create_group_form($from_url);
	if ($mform->is_cancelled()) 
	{
	    $message=get_string('cancelled','block_adminlink');
	    redirect(new moodle_url('/blocks/adminlink/locadmin/create_group.php',array('id'=>$id,'locid'=>$locid)));
	}
	elseif ($data = $mform->get_data())  
	{    
	    $transactioncat_update= $DB->start_delegated_transaction();
        try {
		$record = new stdClass();       
        if($id)
		{
		 $record->id= $id;  
		} 
		$record->name= strtoupper($data->name);		
		$record->idnum= $data->idnum;
		$record->modified_at= time();
		$record->modifier_id= $USER->id;
   	    $record->department=$data->department;
		if($id==0){
		    
		    $record->group_users='0';
		    $record->locid=$locid;
		    $record->created_at= time(); 
		    
		  
	    $log_record->id = $data->change_id;
        $log_record->status = 1;
        $log_record->locid=$locid;
        $log_record->changes = "Create group";
        $log_record->event_name = "Create group with name is ".$data->name;
        $inserted=$DB->update_record('change_track_logs', $log_record);
	        
		$inserted=$DB->insert_record('hierarchy_group', $record);

		//redirect(new moodle_url($from_url));
		}
		else
		{
		$sql= $DB->get_record('hierarchy_group', array('id' => $id));
        $changes ='';
        if($record->name != $sql->name){
            $changes = $changes."Group name ,";
        }
        if($record->idnum != $sql->idnum){
            $changes = $changes."Group ID ,";
        }
        if($record->department != $sql->department){
            $changes = $changes."Department ,";
        }
        $changes = trim($changes,',');
		$log_record->id = $data->change_id;
        $log_record->status = 1;
        $log_record->locid = $locid;
        $log_record->event_name = "Update Group having name ".$data->name;  
        
        $log_record->changes = $changes;
        $inserted=$DB->update_record('change_track_logs', $log_record);
        
		$inserted=$DB->update_record('hierarchy_group', $record);
	
		
		}
		 $transactioncat_update->allow_commit();
		 redirect(new moodle_url('/blocks/adminlink/locadmin/create_group.php',array('locid'=>$locid)));
		}//try ended
        catch(Exception $e)
        {
            $transactioncat_update->rollback($e);
            echo "EXception Thrown";
            exit();
        }
		
		   
	}
	 else{}
	 
	 
		

	$table = new html_table();

	$table->head=array(get_string('sno','block_adminlink'),get_string('id','block_adminlink'),get_string('name','block_adminlink'),get_string('action','block_adminlink'));

	
	

    $sql="select * from mdl_hierarchy_group where active_status=0 and locid =".$locid; 
	   
	  
	   if(isset($_POST['usearch']))
        {
           $from=0;
           $page=0;
            $keysearch=$_REQUEST['usearch'];
         
        	
        }
		$sql.=" and  name LIKE '%".$keysearch."%' ";
       
		$excelquery=$sql. " order by name ";
	
		$sql2= $sql. " order by name limit $from, $paging ";
	  

        
		$orgn=$DB->get_records_sql($sql2);
   
   
   
   
	$sno=$from;
	$check=0;
	if($orgn)
	{
		foreach($orgn as $org)
		{
			$sno++;
				$check=1;
				
		    $button='<a href="create_group.php?id='.$org->id.'&locid='.$locid.'"> <button>'.get_string('edit','block_adminlink').'</button></a>';
			$button.='<a href="group_users.php?id='.$org->id.'&locid='.$locid.'"> <button>'.get_string('add_users','block_adminlink').'</button></a>';
			if(!is_siteadmin())
			{
			$button.='<a href="group_sop.php?id='.$org->id.'&locid='.$locid.'"> <button>'.get_string('add_sop','block_adminlink').'</button></a>';
	    	$button.='<a title="'.get_string('otp_fullform','block_adminlink').'" href="group_otm.php?id='.$org->id.'&locid='.$locid.'"> <button>'.get_string('add_otp','block_adminlink').'</button></a>';
			$button.='<button class="disablegroup" id="grp_'.$org->id.'">'.get_string('disable','block_adminlink').'</button>';
			}

			$row=array($sno,$org->idnum,$org->name, $button);
			$table->data[] =$row;
		}
	}	
	    class download_form extends moodleform
	{

		public function definition() 
		{
			global $excelquery;
							//filter form
				$filterform = $this->_form; // Don't forget the underscore! 

				
				$filterform->addElement('submit','download', 'Download Report',array("class"=>'download'));
				$filterform->addElement('hidden', 'query',$excelquery);
				$filterform->setType('query',PARAM_RAW);
	
		}

		 //Custom validation should be added here

    		function validation($data, $files) 
		{
       			 return array();
		}
	}

	$download=new download_form("excel_group.php");

	$manageurl=new moodle_url('/blocks/adminlink/files/manage_designation.php');
	echo "<div class='custom-heading-div'>".$message;
	echo "<a href='".$CFG->wwwroot."/my/'><button style='float:right;margin-right: 15px;'>".get_string("back","block_adminlink")."</button></a></div>";
	
     /*	if($id!=0){
$baseurl=new moodle_url('/blocks/adminlink/locadmin/create_group.php');
 echo html_writer::start_tag( 'a', array('href' =>$baseurl,'style'=>'float:right' ));
 echo '<button>'.get_string('create_group','block_adminlink').'</button>';
 echo html_writer::end_tag( 'a');
	}*/  
      

	$mform->display();
	echo "<br /><div class='custom-heading-div'>".get_string('available_group','block_adminlink')."</div>";
	
	echo '<div>';
	$download->display();
	echo '</div>';
	
	echo '<form name="searchf" method="post" >';
        echo "<div id='bnu' class='col-md-4 padding-none pull-right text-right'>";                                
        echo '<input type="text" value="'.@$keysearch.'"  placeholder="'.get_string('group_search','block_adminlink').'" name="usearch" id="usearch"/>';
        echo '<input type="submit" name="search" value="'.get_string('search','block_adminlink').'" id="search"/>';
        echo '</div>';
        echo "<div class='clearfix'></div></form> "; 

        echo html_writer::start_tag( 'div', array('class'=>'scroll-bar-wrap'));
		echo html_writer::start_tag( 'div', array('class'=>'popsdatatable'));
	echo html_writer::table($table);
   echo html_writer::end_tag( 'div'); 
    echo html_writer::end_tag( 'div'); 
   
	if($check==0)
   {
        
         echo " <div class='nonation'><b>".get_string('no_data_exist','block_adminlink')."</b></div>";
        
   }
   
    $total_rec=$DB->get_records_sql($sql);
	$rec=count($total_rec);
	$prev=$page-1;
	$next=$page+1;
	$perpage=10;
    $baseurl = new moodle_url($CFG->wwwroot.'/blocks/adminlink/locadmin/create_group.php', array('id'=> $id ,'page' => $page, 'search1'=> $keysearch,'perpage' =>$perpage));
    echo $OUTPUT->paging_bar( $rec, $page, $perpage, $baseurl);
     echo '<div id="dialog1" class="web_dialog"><div class="bg-pop">
 <form method="post" id="auth_form">
   <table class="pop-popover" style="width: 100%; border: 0px;" cellpadding="3" cellspacing="0">
   	<input type="hidden" name="cond" value="0" id="cond">
   	<input type="hidden" name="cond1" value="0" id="cond1">
   	<input type="hidden" name="change_id1" value="0" id="change_id">
   	<input type="hidden" name="custom" value="0" id="custom">
   	<input type="hidden" name="groupid" value="0" id="groupid">
      <tr>
         <td class="web_dialog_title">'.get_string('authentication','block_adminlink').'</td>
         <td class="web_dialog_title align_right">
            <i onclick="HideDialog1()" class="fa fa-times-circle" aria-hidden="true"></i>
         </td>
      </tr>

        <tr>
        <td colspan="2" align="center">
        <div class="form-errors" style="display:none;"></div>                
        </td>
        </tr>
        <tr>
        <td>
        '.get_string('password','block_adminlink').'
        </td>
        <td>
      	<input type="password" placeholder="'.get_string('loggedin_password','block_adminlink').'" name="pass1" value="" id="pass1" style="margin-bottom:15px;">
      	</td>
      	</tr>
	<tr>

	<td style="vertical-align: top;">
        '.get_string('reason','block_adminlink').'
        </td>
	<td>
		<textarea row="3" col="70" name="reason1" placeholder="'.get_string('reason_for_chnge','block_adminlink').'" value="" id="reason1"></textarea>
		</td>
	</tr>	
	
      <tr>
         <td colspan="2" style="text-align: center;">
            <input name="logsubmit" id="logsubmit1" type="button" value="'.get_string('submit','block_adminlink').'" /><br><br>
         </td>
      </tr>
   </table>
   	</form>
</div></div>';
	}
	else
    {
        echo get_string('no_permission','block_adminlink');
    }
echo $OUTPUT->footer(); 
?>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js" type="text/javascript"></script>
<script>
$(document).ready(function ()
   {  
       
       var cus = null;
       //document.getElementById('custom').value;
     
       
       $("#logsubmit1").click(function(){
           
           
           var passw = $('#pass1').val();
           var reasonn = $('#reason1').val();
           var current = window.location.href;
           var  ss =  "Disable Group";//document.getElementById(newid).value;
           var assignadmin=0;
           $.ajax({
                type: "POST",
                url: "<?=$CFG->wwwroot?>/theme/essential/layout/tiles/new.php",
                data: {pass:passw,reason:reasonn,currentLocation:current,event:ss,custom:cus,assignadmin:assignadmin}, 
                dataType: "json",
                //async: false,
                success : function(json)
                { 
                 if(json.data=='false'){
               
                  $('.form-errors').empty();
                  $('.form-errors').show().append(json.msg); 
                  if(json.focus==1){
                    $('#reason1').focus();  
                  }else{
                   $('#pass1').focus();
                  }
                 }else{
                     
                     $('#dialog1').hide();
                     document.getElementById('change_id').value=json.change_id;
                     groupid=document.getElementById('groupid').value;
                     disable(groupid,json.change_id);
                 }
    		                  	  
            } 
               
                
           });
           
       });
        
    $(".disablegroup").click(function() {
        var aa=confirm("<?=get_string('want_to_disable','block_adminlink')?>");
	if(aa){
        var id =this.id; 
        
        var arr = id.split('_');
        document.getElementById('cond1').value=1;
        document.getElementById('groupid').value=arr[1];
        
        var scd = $('#cond').val();
        if(scd==0){
             ShowDialog1(true);
         }
	}
    });    
        
      

      $("#btnClose1").click(function (e)
      {
         HideDialog();
         e.preventDefault();
      });

});

   function ShowDialog1(modal)
   {
       document.getElementById('pass1').value="";
       document.getElementById('reason1').value="";
      $("#overlay").show();
      $("#dialog1").fadeIn(300);
      if (modal)
      {
         $("#overlay").unbind("click");
      }
      else
      {
         $("#overlay").click(function (e)
         {
            HideDialog1();
         });
      }
   }

   function HideDialog1()
   {
      $("#overlay").hide();
      $("#dialog1").fadeOut(300);
   }
/*function assignuser(groupid) 
{   
   alert();
		$.ajax({
            type : "POST",
            url : "assignusers.php",
            data: {group:groupid},
            success : function(data)
            {    
			  alert('Users assigned successfully');
			  
              	  
            } 
        });  		
		
}*/

function disable(groupid,change_id) 
{   
 	
		$.ajax({
            type : "POST",
            url : "disable_group.php",
            data: {group:groupid,change_id:change_id},
            success : function(data)
            {    
			  alert(data);
			  location.reload();
              	  
            } 
        });  		
		
}
function changeanything()
{
        document.getElementById("cond1").value=0;
        document.getElementById("cond").value=0;
        document.getElementById("pass").value='';
        document.getElementById("reason").value='';
}

</script>

	
