<?php
	
	/**
	* ���ã�
		�Ƚ��Լ��û����洢�û���etpǮ����ַ�������׼�¼���洢�������ϵĽ��׼�¼���������ݿ����ӣ���init�������У�����������Ǯ�����û��������룬��queryBlockTrades�������У�
	
	* �������̣�
		�ȴ���Ǯ���˻�:createAccount��������;
		���ࣺ
		    ��������û����е�����etp��ַ������������н��׼�¼������etp��ַ����������ݵ�ַ�鴦�����������еĽ��׼�¼���뱾�ؽ��׼�¼�Աȣ�ɸѡ���µĽ��׼�¼�����浽���ؽ��׼�¼�����ݿ���У�listtxs��������	
		    ���ͽ��ף�send�������� 
	*/
	error_reporting(E_ERROR | E_WARNING | E_PARSE);	  
	
	/**
	*@date 2107-05-02
	*@description ��ѯǮ���˻����
	*@prama $username:�ͻ����û�����$password���ͻ����õ�����
	*/					
	function getbalance($username,$password){
		//���������method:listtx,username:ΪǮ�����û�����password��ΪǮ�������룩  
		$post_data = array(  
		  "method" => "getbalance",  
		  "params" => [$username,$password]  
		);
		return _request($post_data);
	};
	
	/**
	*@date 2107-05-02
	*@description ���ͽ���
	*@prama $username:�ͻ����û�����$password���ͻ����õ����룬address�����յ�ַ��quantity�����
	*/					
	function send($username,$password,$address,$quantity){
		//���������method:listtx,username:ΪǮ�����û�����password��ΪǮ�������룬address�����յ�ַ��quantity����  
		$post_data = array(  
		  "method" => "send",  
		  "params" => [$username,$password,$address,$quantity]  
		);
		return _request($post_data);
	};
	
	/**
	*@date 2107-05-02
	*@description ����Ǯ���˻�
	*@prama $username:�ͻ����û�����$password���ͻ����õ�����
	*/					
	function createAccount($username,$password){
		//���������method:listtx,username:ΪǮ�����û�����password��ΪǮ�������룩  
		$post_data = array(  
		  "method" => "getnewaccount",  
		  "params" => [$username,$password]  
		);
		return _request($post_data);
	};
	
	/**
	*@date 2107-05-02
	*@description ��ѯǮ��������н��׼�¼�����������ϵ����н��׼�¼��;
	*/
	function queryBlockTrades($address){
		//���������method:listtx,username:ΪǮ�����û�����password��ΪǮ�������룬address:ΪǮ���ĵ�ַ��  
		$post_data = array(  
		  "method" => "listtxs",  
		  "params" => ["username","password",$address.""]  
		);
		return _request($post_data);
	};
	
	/**
	*@date 2107-05-02
	*@description ���������
	*/
	function listtxs($conn){
		//��ֵ��ַ��ѯ�����user�������Լ��������ݿ��û���
		$addressSql = 'select id from fanwe_user'; 
		$addresses = select($addressSql,$conn);
		
		//�������ݿ⽻�׼�¼��ѯ��trades�������Լ��������ݿ⽻�׼�¼��
		$tradeSql = 'select hash,address from trades'; 
		$trades = select($tradeSql,$conn);
		
		//���������û��ĵ�ַ
		while($address = mysql_fetch_array($addresses)) {
			//��ѯ�������ϵĽ��׼�¼
			$blockTrades = queryBlockTrades($address);
			$transactions = $blockTrades['transactions'];
			//���������������еĽ��׼�¼
			while($blockTraderow = mysql_fetch_array($transactions)) {
				//�������ش洢�Ľ��׼�¼
				while($localTraderow = mysql_fetch_array($trades)) {
					//�жϱ������������ϵĽ���hash�Ƿ���ͬ
					if($blockTraderow['hash']!=$localTraderow['hash']){
						$outputs = $blockTraderow['hash'];
						//�����������ϵ����
						while($blockOutputrow = mysql_fetch_array($outputs)) {
							//�ж�����뱾�صĵ�ַ�Ƿ���ͬ
							if($localTraderow['address']!=$blockOutputrow['address']){
								//�����㽻��hash�������ַ������ͬʱ,��ʾΪ�µĽ���,���µĽ��ײ��뱾�ؽ������ݿ�
								$localInsertSql = "insert into table(hash,address) values(".$blockTraderow['hash'].",".$blockOutputrow['address'].");";
								insert($localInsertSql,$conn);
							}
						}
					}
				}	
			}
		}
	};
	
	/**
	*@date 2107-05-02
	*@description ��ѯ
	*@prama $sql����ѯ���
	*/					
	function select($sql,$conn){
		//ִ��sql��ѯ
		$result= mysql_query($sql, $conn);
		return $result;
	};
	
	/**
	*@date 2107-05-02
	*@description �������ݿ�
	*/
	function insert($sql, $conn){
		//ִ��sql���
		$result = mysql_query($sql, $conn);
		return $result;
	};
	
	/** 
	* ����post���� 
	* @param string $url �����ַ 
	* @param params��ֵ������ 
	* @return string 
	*/ 
	function _request($params = array()){
		$params = json_encode($params);
		static $ch = null;
		if(is_null($ch)){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; BtcTrade PHP client; ' . php_uname('s') . '; PHP/' . phpversion() . ')');
		}
		# �����趨
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_URL, "http://localhost:8820/rpc");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		return curl_exec($ch);
	}
	
	
	/**
	*@date 2107-05-02
	*@description ��ʼ������
	*/
	function init(){
		//���ݿ�����
		$mysql_name= 'fanwe';
		//���ݿ��û���
		$mysql_username = 'root';
		//���ݿ���������
		$mysql_password = 'root';
		//���ݿ�ip��ַ
		$mysql_server_ip ="localhost";
		//���ӵ����ݿ�
		$conn=mysql_connect($mysql_server_ip, $mysql_username,$mysql_password);
		//�����ݿ�
		mysql_select_db($mysql_name); 
		
		//����Ǯ���˻�
		$account = createAccount("test32","test23");
		//$addressInsertSql = "insert into user(mnemonic,address) values(".$account['mnemonic'].",".$blockOutputrow['default-address'].");";
		//������������Ǯ����ַ���浽���ݿ⵱��
		//insert($addressInsertSql,$conn);
		
		//����˻����
		//send("test32","test23",'dfdbfg',500);
		
		//���������
		//listtxs($conn);
		
	};
	init();
	
?>