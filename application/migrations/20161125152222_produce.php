<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Migration_Produce extends CI_Migration {
    public function up()
    {
        //stafflog
        $this->dbforge->add_field(array(
            'id'=>array('type' => 'varchar','constraint' => 32),
            'staff_id'=>array('type' => 'bigint','constraint' => 20),
            'content'=>array('type' => 'varchar','constraint' => 128,'default'=>''),//操作的描述
            'status'=>array('type' => 'int','constraint' => 11,'default'=>1),//1成功；2失败
            'createdt'=>array('type' => 'datetime','null' => true),
        ));
        $this->dbforge->add_key('id',true);
        $this->dbforge->create_table('pro_stafflog');
        //userflog
        $this->dbforge->add_field(array(
            'id'=>array('type' => 'varchar','constraint' => 32),
            'staff_id'=>array('type' => 'bigint','constraint' => 20),
            'content'=>array('type' => 'varchar','constraint' => 128,'default'=>''),//操作的描述
            'status'=>array('type' => 'int','constraint' => 11,'default'=>1),//1成功；2失败
            'createdt'=>array('type' => 'datetime','null' => true),
        ));
        $this->dbforge->add_key('id',true);
        $this->dbforge->create_table('pro_userlog');
        //Atomins 原子实例: 从客户原始数据导入而来
        $this->dbforge->add_field(array(
            'id'=>array('type' => 'varchar','constraint' => 32),
            'customer_id'=>array('type' => 'bigint','constraint' => 20, 'default'=>0),
            'barcode'=>array('type' => 'varchar','constraint' => 128,'default'=>''),
            'spormp'=>array('type' => 'int','constraint' => 11,'default'=>1),//1：单产品文件；2多产品文件;
            'srcfiledir'=>array('type' => 'varchar','constraint' => 128,'default'=>''),//资源文件所在目录
            'srcfilename'=>array('type' => 'varchar','constraint' => 128,'default'=>''),//资源文件名称
            'filedt'=>array('type' => 'datetime','null' => true),//文件标示时间
            'branch_id'=>array('type' => 'varchar','constraint' => 32,'default'=>''),
            'product_id'=>array('type' => 'bigint','constraint' => 20, 'default'=>0),

            'busicode1'=>array('type' => 'varchar','constraint' => 128,'default'=>''),//主业务编码
            'busicode2'=>array('type' => 'varchar','constraint' => 128,'default'=>''),//辅助业务编码
            'sortingstr'=>array('type' => 'varchar','constraint' => 256,'default'=>''),//排序字符串
            //上封装机的信函的输出张数(在入库时提供,也就是说,先过一遍Pres后再入库)
            'letterpiece'=>array('type' => 'int','constraint' => 11,'default'=>0),//信函的输出张数(便于封装机的生产效率: 实际用时,过滤出单张的和多张的)
            //邮递相对地区(情况1:在入库时客户提供信息直接标注;情况2:需要在生成docins后计算出来;情况3:非信函不需计算和标注)
            'rparea'=>array('type' => 'int','constraint' => 11,'default'=>0),//邮递相对地区:0无关;1待匹配，2本地,3外埠;4境外(客户直接标注:如前海;需在docins中更新的如合众\天安等)
            //关键内容域
            'conti1'=>array('type' => 'int','constraint' => 11,'default'=>0),
            'conti2'=>array('type' => 'int','constraint' => 11,'default'=>0),
            'conti3'=>array('type' => 'int','constraint' => 11,'default'=>0),
            'conts1'=>array('type' => 'varchar','constraint' => 256,'default'=>''),
            'conts2'=>array('type' => 'varchar','constraint' => 256,'default'=>''),
            'conts3'=>array('type' => 'varchar','constraint' => 256,'default'=>''),
            'conts4'=>array('type' => 'varchar','constraint' => 256,'default'=>''),
            'conts5'=>array('type' => 'varchar','constraint' => 256,'default'=>''),
            'conts6'=>array('type' => 'varchar','constraint' => 256,'default'=>''),
            'conts7'=>array('type' => 'varchar','constraint' => 256,'default'=>''),
            'conts8'=>array('type' => 'varchar','constraint' => 256,'default'=>''),
            'conts9'=>array('type' => 'varchar','constraint' => 256,'default'=>''),
            'conts10'=>array('type' => 'varchar','constraint' => 256,'default'=>''),
            'conts11'=>array('type' => 'varchar','constraint' => 256,'default'=>''),
            'conts12'=>array('type' => 'varchar','constraint' => 256,'default'=>''),
            //收件人信息域
            'addressee'=>array('type' => 'varchar','constraint' => 32,'default'=>''),
            'postcode'=>array('type' => 'varchar','constraint' => 16,'default'=>''),
            'address'=>array('type' => 'varchar','constraint' => 128,'default'=>''),
            'email'=>array('type' => 'varchar','constraint' => 64,'default'=>''),
            'tel'=>array('type' => 'varchar','constraint' => 22,'default'=>''),
            'mobile'=>array('type' => 'varchar','constraint' => 22,'default'=>''),

            'status'=>array('type' => 'int','constraint' => 11,'default'=>0),//状态：0入库完成；
						   //1业务规则匹配完成(a/c）；
						   //2检查逻辑完成(查内容逻辑错误,查交付信息错误,查重: 满足条件的都会停下来进入取消状态）；
	                       //3客户确认完成；
	                       //4首发质检匹配拦截完成；
	                       //5运维首批质检完成;
	                       //6单体服务参数配置完成（上邮路的要根据opcenter和postcode配置mailarea；）；
	                       //7运维人员成批确认完成；
	                       //8群体服务参数配置完成；
	                       //9执行生成后续服务完成：可以生成实体作业；可以生成电子作业；可以生成短信作业
            'status0dt'=>array('type' => 'datetime','null' => true),
            'status1dt'=>array('type' => 'datetime','null' => true),
            'status2dt'=>array('type' => 'datetime','null' => true),
            'status3dt'=>array('type' => 'datetime','null' => true),
            'status4dt'=>array('type' => 'datetime','null' => true),
            'status5dt'=>array('type' => 'datetime','null' => true),
            'status6dt'=>array('type' => 'datetime','null' => true),
            'status7dt'=>array('type' => 'datetime','null' => true),
            'status8dt'=>array('type' => 'datetime','null' => true),
            'status9dt'=>array('type' => 'datetime','null' => true),
            //操作人员
            'user3_id'=>array('type' => 'bigint','constraint' => 20, 'default'=>0),//客户确认等操作的人员；
            'staff5_id'=>array('type' => 'bigint','constraint' => 20, 'default'=>0),//运维的工程师；
            'staff7_id'=>array('type' => 'bigint','constraint' => 20, 'default'=>0),//运维的工程师；
            //结果相关
            'resultflag'=>array('type' => 'int','constraint' => 11, 'default'=>0),////0进行中（默认值）；1暂停；2结束；
								//2状态由程序在进入9状态或Cancel状态时自动转入
								//1状态由运维人员调用接口，停住后进行检查用的临时状态；0->1；1->0
            'resultdt'=>array('type' => 'datetime','null' => true),
            
        ));
        $this->dbforge->add_key('id',true);
        $this->dbforge->create_table('pro_atomins');
        //Atomdetail产品明细；在初次入库时，即同步产生此相关记录；作为主产品的部件处理：明细部件
        //每个主产品的所有部件看作一个整体来看待
        $this->dbforge->add_field(array(
            'id'=>array('type' => 'varchar','constraint' => 32),
            'atomins_id'=>array('type' => 'varchar','constraint' => 32),
            'conts1'=>array('type' => 'varchar','constraint' => 256,'default'=>''),
            'conts2'=>array('type' => 'varchar','constraint' => 256,'default'=>''),
        ));
        $this->dbforge->add_key('id',true);
        $this->dbforge->create_table('pro_atomdetail');
        
        //Docins 实物输出实例: 每个产品实例一条记录；id一般等于atominsid
        $this->dbforge->add_field(array(
            'id'=>array('type' => 'varchar','constraint' => 32),
            'customer_id'=>array('type' => 'bigint','constraint' => 20, 'default'=>0),
            'barcode'=>array('type' => 'varchar','constraint' => 128,'default'=>''),
            'spormp'=>array('type' => 'int','constraint' => 11,'default'=>1),//1：单产品文件；2多产品文件;
            'srcfiledir'=>array('type' => 'varchar','constraint' => 128,'default'=>''),//资源文件所在目录
            'srcfilename'=>array('type' => 'varchar','constraint' => 128,'default'=>''),//资源文件名称
            'filedt'=>array('type' => 'datetime','null' => true),//文件标示时间
            'branch_id'=>array('type' => 'varchar','constraint' => 32,'default'=>''),
            'product_id'=>array('type' => 'bigint','constraint' => 20, 'default'=>0),

            'busicode1'=>array('type' => 'varchar','constraint' => 128,'default'=>''),//主业务编码
            'busicode2'=>array('type' => 'varchar','constraint' => 128,'default'=>''),//辅助业务编码
            'sortingstr'=>array('type' => 'varchar','constraint' => 256,'default'=>''),//排序字符串
            //上封装机的信函的输出张数(在入库时提供,也就是说,先过一遍Pres后再入库)
            'letterpiece'=>array('type' => 'int','constraint' => 11,'default'=>0),//信函的输出张数(便于封装机的生产效率: 实际用时,过滤出单张的和多张的)
            //邮递相对地区(情况1:在入库时客户提供信息直接标注;情况2:需要在生成docins后计算出来;情况3:非信函不需计算和标注)
            'rparea'=>array('type' => 'int','constraint' => 11,'default'=>0),//邮递相对地区:0无关;1待匹配，2本地,3外埠;4境外(客户直接标注:如前海;需在docins中更新的如合众\天安等)
            //收件人信息域
            'addressee'=>array('type' => 'varchar','constraint' => 32,'default'=>''),
            'postcode'=>array('type' => 'varchar','constraint' => 16,'default'=>''),
            'address'=>array('type' => 'varchar','constraint' => 128,'default'=>''),
            'email'=>array('type' => 'varchar','constraint' => 64,'default'=>''),
            'tel'=>array('type' => 'varchar','constraint' => 22,'default'=>''),
            'mobile'=>array('type' => 'varchar','constraint' => 22,'default'=>''),

            'status'=>array('type' => 'int','constraint' => 11,'default'=>0),//状态：0创建完成（此时已可开始传输了；非邮路邮件直接进1，attachedfile且非邮区的直接进2）；
	                       //1邮递相对区域确认完成（邮件的本地，外地）；绿色文件直接进2
	                       //2检查取消拦截完成；
	                       //3生产准备完成；（检查文件传递状态和检查取消拦截状态，都完成了就算准备好了；集成器来检查）
	                       //4启动调度完成//产生Job并关联完成
	                       //5生产完成；//Job的最后一个节点完成后，机构业务分拣到分拣柜；个人业务交付投递完成（5、6、7是一个节点，同时赋值）//（查重<5；物料消耗计时点〉=5）
	                       //6封包完成；//（线上加急<6）
						   //7交付投递完成//(紧急取消<7)；
            'atomins0dt'=>array('type' => 'datetime','null' => true),//atomins 的status=0时的时间：入库时间
            'atomins3dt'=>array('type' => 'datetime','null' => true),//atomins 的status=3时的时间：客户确认时间
            'status0dt'=>array('type' => 'datetime','null' => true),
            'status1dt'=>array('type' => 'datetime','null' => true),
            'status2dt'=>array('type' => 'datetime','null' => true),
            'status3dt'=>array('type' => 'datetime','null' => true),
            'status4dt'=>array('type' => 'datetime','null' => true),
            'status5dt'=>array('type' => 'datetime','null' => true),
            'status6dt'=>array('type' => 'datetime','null' => true),
            'status7dt'=>array('type' => 'datetime','null' => true),
            //操作人员
            'staff_id'=>array('type' => 'bigint','constraint' => 20, 'default'=>0),//调度工程师；
            //结果相关
            'resultflag'=>array('type' => 'int','constraint' => 11, 'default'=>0),//0进行中（默认值）；1暂停；2结束；
								//2状态由程序在进入7状态或Cancel状态时自动转入（物料消耗赋值出库的起始状态）
								//1状态由运维人员调用接口，停住后进行检查用的临时状态；0->1；1->0
            'resultdt'=>array('type' => 'datetime','null' => true),

            //过程标识
            'cancelflag'=>array('type' => 'int','constraint' => 11,'default'=>0),//0无关;1取消;2清单中未标识的取消产品,已处理完毕 
            'canceltype'=>array('type' => 'int','constraint' => 11,'default'=>0),//1扣下；2清单勾选，正常寄发；3清单勾选，放顶部寄发
            'canceldt'=>array('type' => 'datetime','null' => true),
            'transflag'=>array('type' => 'int','constraint' => 11,'default'=>0),//文件传标志；0未启动；1已启动；2已确认 
            'outboundflag'=>array('type' => 'int','constraint' => 11,'default'=>0),///物料出库标志；0未处理；1已处理 ；  启动条件为resultflag=2结束 
            'flag1'=>array('type' => 'int','constraint' => 11,'default'=>0),
            'flag2'=>array('type' => 'int','constraint' => 11,'default'=>0),
            'flag3'=>array('type' => 'int','constraint' => 11,'default'=>0),
            'flag4'=>array('type' => 'int','constraint' => 11,'default'=>0),
            'flag5'=>array('type' => 'int','constraint' => 11,'default'=>0),
            'flag6'=>array('type' => 'int','constraint' => 11,'default'=>0),
            'wlflag'=>array('type' => 'int','constraint' => 11,'default'=>0),//已入批次物流信息清单：对于招诺;;已入可打包物流清单：对于大都会
            'fpflag'=>array('type' => 'int','constraint' => 11,'default'=>0),//可回传打印信息的状态；对于大都会

            //物料使用数量，及印量
            'piece1'=>array('type' => 'int','constraint' => 11,'default'=>0),//件数
            'piece2'=>array('type' => 'int','constraint' => 11,'default'=>0),
            'piece3'=>array('type' => 'int','constraint' => 11,'default'=>0),
            'piece4'=>array('type' => 'int','constraint' => 11,'default'=>0),
            'piece5'=>array('type' => 'int','constraint' => 11,'default'=>0),
            'piece6'=>array('type' => 'int','constraint' => 11,'default'=>0),
            'page1'=>array('type' => 'int','constraint' => 11,'default'=>0),//印数
            'page2'=>array('type' => 'int','constraint' => 11,'default'=>0),//印数
            'page3'=>array('type' => 'int','constraint' => 11,'default'=>0),//印数
            'page4'=>array('type' => 'int','constraint' => 11,'default'=>0),//印数
            'page5'=>array('type' => 'int','constraint' => 11,'default'=>0),//印数
            'page6'=>array('type' => 'int','constraint' => 11,'default'=>0),//印数
            
            'pluginitems'=>array('type' => 'varchar','constraint' => 256,'default'=>''),
            
            //物流相关
            'pack_id'=>array('type' => 'varchar','constraint' => 32,'default'=>''),
            'deliverer_id'=>array('type' => 'bigint','constraint' => 20,'default'=>0),
            'delivercode'=>array('type' => 'varchar','constraint' => 32,'default'=>''),
            

        ));
        $this->dbforge->add_key('id',true);
        $this->dbforge->create_table('pro_docins');

        //Docinvoice 与产品关联的单证；在生产Doc时就要生成该表，后期只要选择即可
        $this->dbforge->add_field(array(
            'id'=>array('type' => 'varchar','constraint' => 32),
            'docins_id'=>array('type' => 'varchar','constraint' => 32,'default'=>''),
            'atomdetail_id'=>array('type' => 'varchar','constraint' => 32,'default'=>''),//可以为空
            'material_id'=>array('type' => 'bigint','constraint' => 20,'default'=>0),
            'invoiceins_id'=>array('type' => 'varchar','constraint' => 32,'default'=>''),//可以为空重新调度时要清空
        ));
        $this->dbforge->add_key('id',true);
        $this->dbforge->create_table('pro_docinvoice');

        //Electronicins 电子输出实例: 每个产品实例一条记录；id一般等于atominsid
        //可同时提供多种电子化选项；回传电子版，电邮附件，彩信，短信
        $this->dbforge->add_field(array(
            'id'=>array('type' => 'varchar','constraint' => 32),
            'customer_id'=>array('type' => 'bigint','constraint' => 20, 'default'=>0),
            'barcode'=>array('type' => 'varchar','constraint' => 128,'default'=>''),
            'spormp'=>array('type' => 'int','constraint' => 11,'default'=>1),//1：单产品文件；2多产品文件;
            'srcfiledir'=>array('type' => 'varchar','constraint' => 128,'default'=>''),//资源文件所在目录
            'srcfilename'=>array('type' => 'varchar','constraint' => 128,'default'=>''),//资源文件名称
            'filedt'=>array('type' => 'datetime','null' => true),//文件标示时间
            'branch_id'=>array('type' => 'varchar','constraint' => 32,'default'=>''),
            'product_id'=>array('type' => 'bigint','constraint' => 20, 'default'=>0),

            'busicode1'=>array('type' => 'varchar','constraint' => 128,'default'=>''),//主业务编码
            'busicode2'=>array('type' => 'varchar','constraint' => 128,'default'=>''),//辅助业务编码
            'sortingstr'=>array('type' => 'varchar','constraint' => 256,'default'=>''),//排序字符串
            //上封装机的信函的输出张数(在入库时提供,也就是说,先过一遍Pres后再入库)
            'letterpiece'=>array('type' => 'int','constraint' => 11,'default'=>0),//信函的输出张数(便于封装机的生产效率: 实际用时,过滤出单张的和多张的)
            //邮递相对地区(情况1:在入库时客户提供信息直接标注;情况2:需要在生成docins后计算出来;情况3:非信函不需计算和标注)
            'rparea'=>array('type' => 'int','constraint' => 11,'default'=>0),//邮递相对地区:0无关;1待匹配，2本地,3外埠;4境外(客户直接标注:如前海;需在docins中更新的如合众\天安等)
            //收件人信息域
            'addressee'=>array('type' => 'varchar','constraint' => 32,'default'=>''),
            'postcode'=>array('type' => 'varchar','constraint' => 16,'default'=>''),
            'address'=>array('type' => 'varchar','constraint' => 128,'default'=>''),
            'email'=>array('type' => 'varchar','constraint' => 64,'default'=>''),
            'tel'=>array('type' => 'varchar','constraint' => 22,'default'=>''),
            'mobile'=>array('type' => 'varchar','constraint' => 22,'default'=>''),

            'status'=>array('type' => 'int','constraint' => 11,'default'=>0),//状态：0创建记录完成；
                           //1前提约束条件完成（满足，如等待实体生产完成；需真实发票号码或需在实体完成交付后，才能到达此状态）；
	                       //2调度生成原始文件；
	                       //3生成电子版完成；
            'atomins0dt'=>array('type' => 'datetime','null' => true),//atomins 的status=0时的时间：入库时间
            'atomins3dt'=>array('type' => 'datetime','null' => true),//atomins 的status=3时的时间：客户确认时间
            'status0dt'=>array('type' => 'datetime','null' => true),
            'status1dt'=>array('type' => 'datetime','null' => true),
            'status2dt'=>array('type' => 'datetime','null' => true),
            'status3dt'=>array('type' => 'datetime','null' => true),
            //操作人员
            'staff_id'=>array('type' => 'bigint','constraint' => 20, 'default'=>0),//调度工程师；
            //结果相关
            'resultflag'=>array('type' => 'int','constraint' => 11, 'default'=>0),//0进行中（默认值）；1暂停；2结束；
								//2状态由程序在进入3状态时并且returnflag和emailflag为0或2时自动转入（返回电子版或电邮电子版集成器来更新）
								//1状态由运维人员调用接口，停住后进行检查用的临时状态；0->1；1->0
            'resultdt'=>array('type' => 'datetime','null' => true),

            //过程标识
            'returnflag'=>array('type' => 'int','constraint' => 11,'default'=>0),//返还电子版标志：0不需；1需要返还；2完成返还 
            'returndt'=>array('type' => 'datetime','null' => true),
            'emailflag'=>array('type' => 'int','constraint' => 11,'default'=>0),//电邮电子版标志：0不需；1需要电邮；2完成电邮电子版 
            'emaildt'=>array('type' => 'datetime','null' => true),
            'flag1'=>array('type' => 'int','constraint' => 11,'default'=>0),
            'flag2'=>array('type' => 'int','constraint' => 11,'default'=>0),
            'flag3'=>array('type' => 'int','constraint' => 11,'default'=>0),

        ));
        $this->dbforge->add_key('id',true);
        $this->dbforge->create_table('pro_electronicins');
        
    }

    
    public function down()
    {
        $this->dbforge->drop_table('pro_electronicins');
        $this->dbforge->drop_table('pro_docinvoice');
        $this->dbforge->drop_table('pro_docins');
        $this->dbforge->drop_table('pro_atomdetail');

        $this->dbforge->drop_table('pro_atomins');
        $this->dbforge->drop_table('pro_userlog');
        $this->dbforge->drop_table('pro_stafflog');
    }
    
}