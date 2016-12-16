<?php
class Produce_m extends CI_Model {

    public function __construct(){
//        $this->load->database();
        $this->rdb = $this->load->database('rdb',TRUE);
        $this->wdb = $this->load->database('wdb',TRUE);
    }
    //产生日志
    public function gen_userlog($userid,$content){
        $dt=new DateTime();
        $data = array(
            'user_id' => $userid,
            'content' => $content,
            'createdt' => $dt->format('Y-m-d H:i:s'),
        );
        $this->wdb->insert('bas_userlog', $data);
    }        
    public function lookup($type,$typeid){
        $result='';
        $sql='SELECT * FROM bas_lookup where type = ? and typeid = ? ';
        $query=$this->rdb->query($sql,array($type,$typeid));
        $row=$query->row_array();
        if($row != null) $result=$row['name'];
        return $result;
    }
    public function get_user($accountno){
        $sql='SELECT * FROM bas_user where active=1 and status=3 ';
        $sql=$sql.' and (accountno = ? or email = ?) ';
        $query=$this->rdb->query($sql,array($accountno,$accountno));
        return $query->row_array();
    }
    public function sendemail($email){
        $resultno=1;
        $dt=new DateTime();
        
        $this->load->library('email');
        $this->email->set_newline("\r\n");

        $sql='SELECT * FROM bas_user where email = ? ';
        $query=$this->rdb->query($sql,array($email));
        $user=$query->row_array();
        if($user != null){//老用户，重置密码的邮件
            $resetdt=new DateTime();
            $resetdt->modify("-60 minute");
            
            $sql='SELECT * FROM bas_user where id='.$user['id'];
            $sql=$sql.' and resetpswddt > ? ';
            $query=$this->rdb->query($sql,array($resetdt->format('Y-m-d H:i:s')));
            $user0=$query->row_array();
            if($user0 != null)$resultno=13;
            else{
                $rcode=$this->randStr();
                $this->wdb->set('resetpswdcode',$rcode);
                $this->wdb->set('resetpswddt',$dt->format('Y-m-d H:i:s'));
                $this->wdb->where('id',$user['id']);
                $this->wdb->update('bas_user');
                
                if($user['status']>1){//重置密码邮件
                    $this->email->from('teampal@sina.com','TEAMPAL');
                    $this->email->to($email);
                    $this->email->subject('众志成城 重置密码验证邮件');
                    $this->email->message('您的验证码为：'.$rcode.'，请尽快重置密码！
<div id="introduction" style="position: absolute; top: 40px; left: 10px; right: 10px; height: 80px;">
    <p class="fs_label" style="position: absolute; top: 10px; left: 20px;"><a href="http://www.teampal.cn">众志成城</a>是团队高效敏捷方法的效率工具。</p>
    <p class="fs_label" style="position: absolute; top: 30px; left: 20px; color: red;">让团队活动更优雅。</p>
</div>
');
                    $this->email->send();
                    $this->gen_userlog($user['id'],'重置密码');
                }else{//人工确认邮件
                    $this->email->from('teampal@sina.com','TEAMPAL');
                    $this->email->to($email);
                    $this->email->subject('众志成城 用户申请确认邮件');
                    $this->email->message('您的申请我们已经收到，我们将尽快完成确认事项，并发送邮件到该邮箱，请随时关注此邮箱，谢谢！
<div id="introduction" style="position: absolute; top: 40px; left: 10px; right: 10px; height: 80px;">
    <p class="fs_label" style="position: absolute; top: 10px; left: 20px;"><a href="http://www.teampal.cn">众志成城</a>是团队高效敏捷方法的效率工具。</p>
    <p class="fs_label" style="position: absolute; top: 30px; left: 20px; color: red;">让团队活动更优雅。</p>
</div>
');
                    $this->email->send();
                    $this->gen_userlog($user['id'],'重置密码');
                }
            }
        }else{//新用户，注册邮件
            log_message('error',':::user::4:');
            $sql='SELECT * FROM bas_config where id=1 ';
            $query=$this->rdb->query($sql);
            $sysconfig=$query->row_array();
            
            $rcode=$this->randStr();
            $udata=array(
                'name'=>$email,
                'email'=>$email,
                'status'=>1,
                'active'=>1,
                'language'=>2,
                'status1dt'=>$dt->format('Y-m-d H:i:s'),
                'resetpswdcode'=>$rcode,
                'resetpswddt'=>$dt->format('Y-m-d H:i:s')
            );
            $this->wdb->insert('bas_user',$udata);
            $userid=$this->wdb->insert_id();
            if($userid != null and $userid > 1){
                //初始化用户：用户名、用户号、创建伙伴
                $namestrs=explode('@',$email);
                $yearstr=$dt->format('Y');
                if($sysconfig['autoconfirm']==1){
                    $this->wdb->set('status',2);
                    $this->wdb->set('status2dt',$dt->format('Y-m-d H:i:s'));
                }
                $this->wdb->set('name',$namestrs[0]);
                $this->wdb->set('accountno',substr($yearstr,-2).$userid);
                $this->wdb->where('id',$userid);
                $this->wdb->update('bas_user');
                
                $bdata=array(
                    'user_id'=>$userid,
                    'buser_id'=>$userid,
                    'createdt'=>$dt->format('Y-m-d H:i:s')
                );
                $this->wdb->insert('bas_buddy',$bdata);
                //初始化结束

                if($sysconfig['autoconfirm']==1){//发送注册验证邮件
                    $this->email->from('teampal@sina.com','TEAMPAL');
                    $this->email->to($email);
                    $this->email->subject('众志成城 用户注册验证邮件');
                    $this->email->message('您的帐号为：'.substr($yearstr,-2).$userid.'。验证码为：'.$rcode.'，请尽快完成注册！
<div id="introduction" style="position: absolute; top: 40px; left: 10px; right: 10px; height: 80px;">
    <p class="fs_label" style="position: absolute; top: 10px; left: 20px;"><a href="http://www.teampal.cn">众志成城</a>是团队高效敏捷方法的效率工具。</p>
    <p class="fs_label" style="position: absolute; top: 30px; left: 20px; color: red;">让团队活动更优雅。</p>
</div>
');
                    $this->email->send();
                    $this->gen_userlog($userid,'注册验证邮件');
                }else{//人工确认申请
                    $this->email->from('teampal@sina.com','TEAMPAL');
                    $this->email->to($email);
                    $this->email->subject('众志成城 用户申请确认邮件');
                    $this->email->message('您的申请我们已经收到，我们将尽快完成确认事项，并发送邮件到该邮箱，请随时关注此邮箱，谢谢！
<div id="introduction" style="position: absolute; top: 40px; left: 10px; right: 10px; height: 80px;">
    <p class="fs_label" style="position: absolute; top: 10px; left: 20px;"><a href="http://www.teampal.cn">众志成城</a>是团队高效敏捷方法的效率工具。</p>
    <p class="fs_label" style="position: absolute; top: 30px; left: 20px; color: red;">让团队活动更优雅。</p>
</div>
');
                    $this->email->send();
                    $this->gen_userlog($userid,'用户申请确认邮件');
                }
            }
        }
        return $resultno;
    }
    public function send_email($userid,$email,$titile,$content){//通用发邮件
        $resultno=1;
        $dt=new DateTime();
        
        $this->load->library('email');
        $this->email->set_newline("\r\n");

        $this->email->from('teampal@sina.com','TEAMPAL');
        $this->email->to($email);
        $this->email->subject($titile);
        $this->email->message($content);
        $this->email->send();

        $this->gen_userlog($userid,'发送邮件：'.$email);

        return $resultno;
    }
    public function set_password($email,$yzm,$password){
        $resultno=1;
        $dt=new DateTime();
        
        $sql='SELECT * FROM bas_user where email = ? and resetpswdcode = ?';
        $query=$this->rdb->query($sql,array($email,$yzm));
        $user=$query->row_array();
        if($user!=null){//找到用户
            if($user['status']==2){//新注册成功用户password_hash('belstar',PASSWORD_DEFAULT)
                $this->wdb->set('password',password_hash($password,PASSWORD_DEFAULT));
                $this->wdb->set('resetpswdcode',password_hash($yzm,PASSWORD_DEFAULT));
                $this->wdb->set('resetpswddt',$dt->format('Y-m-d H:i:s'));
                $this->wdb->set('status',3);
                $this->wdb->set('status3dt',$dt->format('Y-m-d H:i:s'));
                $this->wdb->where('id',$user['id']);
                $this->wdb->update('bas_user');
                $resultno=2;
            }else{
                $this->wdb->set('password',password_hash($password,PASSWORD_DEFAULT));
                $this->wdb->set('resetpswdcode',password_hash($yzm,PASSWORD_DEFAULT));
                $this->wdb->set('resetpswddt',$dt->format('Y-m-d H:i:s'));
                $this->wdb->where('id',$user['id']);
                $this->wdb->update('bas_user');
                $resultno=3;
            }
            $this->gen_userlog($user['id'],'重置密码');
        }else{
            $resultno=11;//验证码错误
        }

        return $resultno;
    }
    
    //buddy相关
    public function get_invites($userid){
        $sql = "SELECT * FROM bas_invite WHERE user_id = ?  order by id desc ";
        $query=$this->rdb->query($sql, array($userid));

        return $query->result_array();
    }
    public function get_inviteds($userid,$useremail,$invitestatus1,$invitestatus2,$invitestatus3){
        $arraystatus=array(0);
        if($invitestatus1==1) array_push($arraystatus,1);
        if($invitestatus2==1) array_push($arraystatus,2);
        if($invitestatus3==1) array_push($arraystatus,3);
        
        $sql = "SELECT i.id as id,i.status as status,i.memo as memo,u.name as username,u.email as useremail FROM bas_invite i, bas_user u WHERE i.user_id=u.id and i.email = ? AND i.status IN ?  order by i.id desc ";
        $query=$this->rdb->query($sql, array($useremail, $arraystatus));

        return $query->result_array();
    }
    public function get_buddies($userid,$useremail,$buddyactive0,$buddyactive1){
        $arrayactive=array(9);
        if($buddyactive0==1) array_push($arrayactive,0);
        if($buddyactive1==1) array_push($arrayactive,1);
        
        $sql = "SELECT b.id as id,b.active as active,u.name as username,u.email as useremail FROM bas_buddy b, bas_user u WHERE b.buser_id=u.id and b.user_id = ? AND b.active IN ? ";
        $query=$this->rdb->query($sql, array($userid, $arrayactive));
        $result=$query->result_array();
        return $result;
    }
    public function activate_buddy($buddyid,$userid){
        $resultno=0;
                
        $query=$this->rdb->get_where('bas_buddy',array('id'=>$buddyid,'user_id'=>$userid));
        $thebuddy=$query->row_array();
        if ($thebuddy != NULL){
            $newactive=1;
            if($thebuddy['active']==1) $newactive=0;
            
            $sql = "UPDATE bas_buddy SET active = ".$newactive." WHERE id = ".$buddyid;
            $query=$this->wdb->query($sql);
            $resultno=1;

            $this->gen_userlog($userid,'激活/屏蔽伙伴');
        }
        return $resultno;
    }
    public function decline_invite($inviteid,$userid,$useremail){
        $resultno=0;
        $dt=new DateTime();

        $query=$this->rdb->get_where('bas_invite',array('id'=>$inviteid));
        $invite=$query->row_array();
        if ($invite != NULL and $invite['email'] == $useremail){
            $this->wdb->set('status',3);
            $this->wdb->set('status3dt',$dt->format('Y-m-d H:i:s'));
            $this->wdb->where('id',$inviteid);
            $this->wdb->update('bas_invite');
            $resultno=1;
            
            $this->gen_userlog($userid,'拒绝成为伙伴的邀请');
        }
        return $resultno;
    }
    public function accept_invite($inviteid,$userid,$useremail){
        $resultno=0;
                
        $dt=new DateTime();
        $query=$this->rdb->get_where('bas_invite',array('id'=>$inviteid));
        $invite=$query->row_array();
        if ($invite != NULL and $invite['email'] == $useremail){
            $this->wdb->set('status',2);
            $this->wdb->set('status2dt',$dt->format('Y-m-d H:i:s'));
            $this->wdb->where('id',$inviteid);
            $this->wdb->update('bas_invite');

            $sql='SELECT * FROM bas_buddy where user_id= ? and buser_id = ? ';
            $query=$this->rdb->query($sql,array($userid,$invite['user_id']));
            $buddys=$query->result_array();
            if(count($buddys)==0){//还未建立
                $bdata=array(
                    'user_id'=>$userid,
                    'buser_id'=>$invite['user_id'],
                    'createdt'=>$dt->format('Y-m-d H:i:s')
                );
                $this->wdb->insert('bas_buddy',$bdata);
                $bdata=array(
                    'user_id'=>$invite['user_id'],
                    'buser_id'=>$userid,
                    'createdt'=>$dt->format('Y-m-d H:i:s')
                );
                $this->wdb->insert('bas_buddy',$bdata);

                $this->gen_userlog($userid,'接受邀请，成为伙伴');
            }
            $resultno=1;
        }
        return $resultno;
    }
    //Invite
    public function do_invite($email,$memo,$userid,$useremail){
        $resultno=0;
        $dt=new DateTime();
        $this->load->library('email');
        $this->email->set_newline("\r\n");

        $query=$this->rdb->get_where('bas_invite',array('user_id'=>$userid,'email'=>$email,'status'=>1));
        $invites=$query->result_array();
        if(count($invites)==0){//还未发出
            $bdata=array(
                'user_id'=>$userid,
                'email'=>$email,
                'memo'=>$memo,
                'status'=>1,
                'status1dt'=>$dt->format('Y-m-d H:i:s')
            );
            $this->wdb->insert('bas_invite',$bdata);
            //发邀请邮件
            $this->email->from('teampal@sina.com','TEAMPAL');
            $this->email->to($email);
            $this->email->subject($useremail.'邀请您使用“众志成城”');
            $this->email->message('您的朋友('.$useremail.')，邀请您使用“众志成城”。
<div id="introduction" style="position: absolute; top: 40px; left: 10px; right: 10px; height: 80px;">
    <p class="fs_label" style="position: absolute; top: 10px; left: 20px;"><a href="http://www.teampal.cn">众志成城</a>是团队高效敏捷方法的效率工具。</p>
    <p class="fs_label" style="position: absolute; top: 30px; left: 20px; color: red;">让团队活动更优雅。</p>
</div>
');
            $this->email->send();
            
            $resultno=1;
            $this->gen_userlog($userid,'向'.$email.'发出邀请');
        }
        return $resultno;
    }
    public function cancel_invite($inviteid,$userid){
        $resultno=0;
        $dt=new DateTime();

        $query=$this->rdb->get_where('bas_invite',array('user_id'=>$userid,'id'=>$inviteid,'status'=>1));
        $invite=$query->row_array();
        if($invite != null){//还未处理
            $this->wdb->set('status',4);
            $this->wdb->set('status4dt',$dt->format('Y-m-d H:i:s'));
            $this->wdb->where('id',$inviteid);
            $this->wdb->update('bas_invite');
            
            $resultno=1;
            $this->gen_userlog($userid,'放弃邀请'.$invite['email']);
        }
        return $resultno;
    }

    //scrum相关
    public function get_msscrums(){//所有Scrums
        $sql = 'SELECT * FROM bas_msscrum';
        $query=$this->rdb->query($sql);

        return $query->result_array();
    }
    public function get_mymsscrums($userid){//为我服务或为我伙伴服务的会议微服务实例；buddy中usertype>1的user，
        $sql = 'SELECT * FROM bas_msscrum where id in (select msscrum_id from bas_user where active=1 and usertype > 1 and id in (select buser_id from bas_buddy where user_id='.$userid.' and active=1))';
        $query=$this->rdb->query($sql);

        return $query->result_array();
    }
    public function get_msscrum($msscrumid){
        $sql = 'SELECT * FROM bas_msscrum where id = '.$msscrumid;
        $query=$this->rdb->query($sql);

        return $query->row_array();
    }
    
    //circle相关
    public function get_mscircles(){//所有Circles
        $sql = 'SELECT * FROM bas_mscircle';
        $query=$this->rdb->query($sql);

        return $query->result_array();
    }
    public function get_mymscircles($userid){//为我服务或为我伙伴服务的会议微服务实例；buddy中usertype>1的user，
        $sql = 'SELECT * FROM bas_mscircle where id in (select mscircle_id from bas_user where active=1 and usertype > 1 and id in (select buser_id from bas_buddy where user_id='.$userid.' and active=1))';
        $query=$this->rdb->query($sql);

        return $query->result_array();
    }
    public function get_mscircle($mscircleid){
        $sql = 'SELECT * FROM bas_mscircle where id = '.$mscircleid;
        $query=$this->rdb->query($sql);

        return $query->row_array();
    }
    
    //meeting相关
    public function get_msmeetings(){//所有Meetinges
        $sql = 'SELECT * FROM bas_msmeeting';
        $query=$this->rdb->query($sql);

        return $query->result_array();
    }
    public function get_mymsmeetings($userid){//为我服务或为我伙伴服务的会议微服务实例；buddy中usertype>1的user，
        $sql = 'SELECT * FROM bas_msmeeting where id in (select msmeeting_id from bas_user where active=1 and usertype > 1 and id in (select buser_id from bas_buddy where user_id='.$userid.' and active=1))';
        $query=$this->rdb->query($sql);

        return $query->result_array();
    }
    public function get_msmeeting($msmeetingid){
        $sql = 'SELECT * FROM bas_msmeeting where id = '.$msmeetingid;
        $query=$this->rdb->query($sql);

        return $query->row_array();
    }
    public function get_cand_buddys($userid){//待选活动伙伴
        $sql = 'SELECT a.id as id,a.buser_id as buser_id,b.name as name,b.email as email,0 as include,b.name as membername FROM bas_buddy a, bas_user b where a.buser_id=b.id and a.active=1 and a.user_id='.$userid;
        $query=$this->rdb->query($sql);

        return $query->result_array();
    }
    public function get_buddy_vipusers($userid){//buddy中usertype>1的user，不包括自己
        $sql = 'SELECT * FROM bas_user where active=1 and usertype > 1 and id<>'.$userid.' and id in (select buser_id from bas_buddy where user_id='.$userid.' and active=1)';
        $query=$this->rdb->query($sql);

        return $query->result_array();
    }
  

    
    //Me
    public function get_myself($userid){
        $sql='SELECT * FROM bas_user where id = ? ';
        $query=$this->rdb->query($sql,array($userid));
        return $query->row_array();
    }
      public function modify_me($name,$email,$userid){
        $resultno=1;
        $dt=new DateTime();
        //查看是否被使用
        $sql='SELECT * FROM bas_user where email = ? and id <> ?';
        $query=$this->rdb->query($sql,array($email,$userid));
        $user=$query->row_array();
        if($user!=null)$resultno=2;//已使用
        else{
            $this->wdb->set('name',$name);
            $this->wdb->set('email',$email);
            $this->wdb->where('id',$userid);
            $this->wdb->update('bas_user');
        }

        return $resultno;
    }
    public function feedback($thefeedback,$userid){
        $resultno=1;
        $dt=new DateTime();
        //查看是否太多重复
        $resetdt=new DateTime();
        $resetdt->modify("-60 minute");
        $sql='SELECT * FROM bas_feedback where createdt > ? and user_id = ?';
        $query=$this->rdb->query($sql,array($resetdt->format('Y-m-d H:i:s'),$userid));
        $feedbacks=$query->result_array();
        if(count($feedbacks)>0)$resultno=13;//太频繁
        else{
            $bdata=array(
                'user_id'=>$userid,
                'feedback'=>$thefeedback,
                'createdt'=>$dt->format('Y-m-d H:i:s')
            );
            $this->wdb->insert('bas_feedback',$bdata);

            $this->gen_userlog($userid,'提交反馈');
        }

        return $resultno;
    }


     //Operation 
     //setup
     public function get_config(){
        $sql='SELECT * FROM bas_config where id=1 ';
        $query=$this->rdb->query($sql);
        return $query->row_array();
    }
    public function modify_config($userid,$autoconfirm,$status,$versionno){
        $resultno=1;
        if($userid==1){
            $this->wdb->set('autoconfirm',$autoconfirm);
            $this->wdb->set('status',$status);
            $this->wdb->set('versionno',$versionno);
            $this->wdb->where('id',1);
            $this->wdb->update('bas_config');
        }

        return $resultno;
    }


    //mscircle
    public function update_mscircle($id,$name,$user,$pass,$url,$port){//
        $resultno=1;
        
        $this->wdb->set('name',$name);
        $this->wdb->set('user',$user);
        $this->wdb->set('pass',$pass);
        $this->wdb->set('url',$url);
        $this->wdb->set('port',$port);
        $this->wdb->where('id',$id);
        $this->wdb->update('bas_mscircle');

        return $resultno;
    }
    //msscrum
    public function update_msscrum($id,$name,$user,$pass,$url,$port){//
        $resultno=1;
        
        $this->wdb->set('name',$name);
        $this->wdb->set('user',$user);
        $this->wdb->set('pass',$pass);
        $this->wdb->set('url',$url);
        $this->wdb->set('port',$port);
        $this->wdb->where('id',$id);
        $this->wdb->update('bas_msscrum');

        return $resultno;
    }
    //msmeeting
    public function update_msmeeting($id,$name,$user,$pass,$url,$port){//
        $resultno=1;
        
        $this->wdb->set('name',$name);
        $this->wdb->set('user',$user);
        $this->wdb->set('pass',$pass);
        $this->wdb->set('url',$url);
        $this->wdb->set('port',$port);
        $this->wdb->where('id',$id);
        $this->wdb->update('bas_msmeeting');

        return $resultno;
    }


    //Oper users
    public function get_users($forname,$foremail,$ua0,$ua1,$us1,$us2,$us3,$ut0,$ut1,$ut2,$ut3,$page,$psize){
        $result=array();
        
        $filterstr='(1=1)';
        if(strlen($forname) > 1) $filterstr = $filterstr. ' and (name like "'.$forname.'%")';
        if(strlen($foremail) > 1) $filterstr = $filterstr. ' and (email like "'.$foremail.'%")';
        if($ua0==0) $filterstr = $filterstr. ' and (active <> 0)';
        if($ua1==0) $filterstr = $filterstr. ' and (active <> 1)';
        if($us1==0) $filterstr = $filterstr. ' and (status <> 1)';
        if($us2==0) $filterstr = $filterstr. ' and (status <> 2)';
        if($us3==0) $filterstr = $filterstr. ' and (status <> 3)';
        if($ut0==0) $filterstr = $filterstr. ' and (usertype <> 0)';
        if($ut1==0) $filterstr = $filterstr. ' and (usertype <> 1)';
        if($ut2==0) $filterstr = $filterstr. ' and (usertype <> 2)';
        if($ut3==0) $filterstr = $filterstr. ' and (usertype <> 3)';

        $sql = 'select count(*) as thecount from bas_user where '.$filterstr;
        $query=$this->rdb->query($sql);
        $temp=$query->row_array();
        $count=0;
        if($temp!=null) $count=$temp['thecount'];
        
        $thepy=$page*$psize;
        if($thepy>=$count){
            $thepy=0;
            $page=0;
        }

        $sql = 'select * from bas_user where '.$filterstr.' limit '.$thepy.','.$psize;
        $query=$this->rdb->query($sql);
        $users=$query->result_array();
        
        $result['users']=$users;
        $result['count']=$count;
        $result['page']=$page;
        return $result;
    }
    public function changestatus_user($userid){//active 1 -> 2确认
        $resultno=1;
        $dt=new DateTime();
        
        $sql='SELECT * FROM bas_user where id = ?';
        $query=$this->rdb->query($sql,array($userid));
        $user=$query->row_array();
        if($user!=null and $user['status']==1){
            $this->wdb->set('status',2);
            $this->wdb->set('status2dt',$dt->format('Y-m-d H:i:s'));
            $this->wdb->where('id',$userid);
            $this->wdb->update('bas_user');
        }

        return $resultno;
    }
    public function update_user($userid,$usertype,$active,$scaleout,$mscircleid,$msscrumid,$msmeetingid){//
        $resultno=1;
        
        $this->wdb->set('usertype',$usertype);
        $this->wdb->set('active',$active);
        $this->wdb->set('scaleout',$scaleout);
        $this->wdb->set('mscircle_id',$mscircleid);
        $this->wdb->set('msscrum_id',$msscrumid);
        $this->wdb->set('msmeeting_id',$msmeetingid);
        $this->wdb->where('id',$userid);
        $this->wdb->update('bas_user');

        return $resultno;
    }
 
     //Oper feedbacks
    public function get_feedbacks($foremail,$feedbackstatus1,$feedbackstatus2,$page,$psize){
        $result=array();
        
        $filterstr='(1=1)';
        if(strlen($foremail) > 1) $filterstr = $filterstr. ' and (u1.email like "'.$foremail.'%")';
        if($feedbackstatus1==0) $filterstr = $filterstr. ' and (t.status <> 1)';
        if($feedbackstatus2==0) $filterstr = $filterstr. ' and (t.status <> 2)';

        $sql = 'select count(*) as thecount 
 from bas_feedback t,bas_user u1 where t.user_id=u1.id and '.$filterstr;
        $query=$this->rdb->query($sql);
        $temp=$query->row_array();
        $count=0;
        if($temp!=null) $count=$temp['thecount'];
        
        $thepy=$page*$psize;
        if($thepy>=$count){
            $thepy=0;
            $page=0;
        }

        $sql = 'select t.id as id, t.feedback as feedback,t.createdt as createdt,t.handlememo as handlememo,t.status as status,u1.name as name,u1.email as email  
 from bas_feedback t,bas_user u1 where t.user_id=u1.id and '.$filterstr.' limit '.$thepy.','.$psize;
        $query=$this->rdb->query($sql);
        $feedbacks=$query->result_array();
        
        $result['feedbacks']=$feedbacks;
        $result['count']=$count;
        $result['page']=$page;
        return $result;
    }
    public function update_feedback($id,$status,$handlememo){//status 1 --> 2
        $resultno=1;
        $dt=new DateTime();
        
        if($status==2){
            $this->wdb->set('handledt',$dt->format('Y-m-d H:i:s'));
        }
        $this->wdb->set('status',$status);
        $this->wdb->set('handlememo',$handlememo);
        $this->wdb->where('id',$id);
        $this->wdb->update('bas_feedback');

        return $resultno;
    }

     //Oper userlogs
    public function get_userlogs($psize){
        $result=array();
        $page= isset($_GET['page']) ? $_GET['page'] : 0;
        
        $fromdt=new DateTime();
        $todt=new DateTime();
        $fromdt->modify('-1 month');
        $filterstr='(1=1)';
        if(strlen($_SESSION['forfromdt']) > 9) $fromdt=new DateTime($_SESSION['forfromdt']);
        if(strlen($_SESSION['fortodt']) > 9) $todt=new DateTime($_SESSION['fortodt']);
        $filterstr = $filterstr. ' and (t.createdt > ? ) and (t.createdt < ? ) ';
        if(strlen($_SESSION['forcont']) > 1) $filterstr = $filterstr. ' and (t.content like "%'.$_SESSION['forcont'].'%")';
        if(strlen($_SESSION['foremail']) > 1) $filterstr = $filterstr. ' and (u1.email like "'.$_SESSION['foremail'].'%")';

        $sql = 'select count(*) as thecount 
 from bas_userlog t,bas_user u1 where t.user_id=u1.id and '.$filterstr;
        $query=$this->rdb->query($sql,array($fromdt->format('Y-m-d H:i:s'),$todt->format('Y-m-d H:i:s')));
        $temp=$query->row_array();
        $count=0;
        if($temp!=null) $count=$temp['thecount'];
        
        $thepy=$page*$psize;
        if($thepy>=$count){
            $thepy=0;
            $page=0;
        }

        $sql = 'select t.id as id, t.content as content,t.createdt as createdt,u1.name as name,u1.email as email  
 from bas_userlog t,bas_user u1 where t.user_id=u1.id and '.$filterstr.' limit '.$thepy.','.$psize;
        $query=$this->rdb->query($sql,array($fromdt->format('Y-m-d H:i:s'),$todt->format('Y-m-d H:i:s')));
        $userlogs=$query->result_array();
        
        $result['userlogs']=$userlogs;
        $result['count']=$count;
        $result['page']=$page;
        return $result;
    }



    public function backup_db(){
        $resultno=1;
        $dt=new DateTime();

        $filename=$fromdt->format('YmdHis');
        if(strlen($filename)>2){
            exec('mysqldump -uteampaluser -pteampal --default-character-set=utf8 team_base > '.$filename.'.sql');
        }

        return $resultno;
    }
    public function restore_db($filename){
        $resultno=1;
        $dt=new DateTime();

        if(strlen($filename)>2){
            exec('mysql -uteampaluser -pteampal team_base < '.$filename.'.sql');
        }

        return $resultno;
    }
    //生成6位随机数
    public function randStr() { 
/**
        switch($format) { 
            case 'ALL':
                $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~'; break;
            case 'CHAR':
                $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-@#~'; break;
            case 'NUMBER':
                $chars='0123456789'; break;
            default :
                $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~'; 
                break;
        }
**/
        $chars='0123456789';
        mt_srand((double)microtime()*1000000*getmypid()); 
        $password="";
        while(strlen($password)<6)
            $password.=substr($chars,(mt_rand()%strlen($chars)),1);
        return $password;
    }     


  
}