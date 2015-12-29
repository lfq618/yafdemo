<?php
class socketLib
{
	private $host = '127.0.0.1';
	private $port = 8080;
	private $maxuser = 10;
	public  $accept = array();
	private $cycle  = array();
	private $isHand = array();
	
	public $socket = null;
	
	public $funcs = array();
	
	function __construct($host, $port, $maxuser)
	{
		$this->host = $host;
		$this->port = $port;
		$this->maxuser = $maxuser;
	}
	
	//挂起socket
	public function start_server()
	{
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		
		//允许使用本地地址
		socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, true);
		socket_bind($this->socket, $this->host, $this->port);
		//最多10个人链接，超过的客户端会返回WSAECONNREFUS错误
		socket_listen($this->socket, $this->maxuser);
		while (true)
		{
			$this->cycle = $this->accept;
			$this->cycle[] = $this->socket;
			//阻塞用，有新链接时才会结束
			socket_select($this->cycle, $write, $except, null);
			foreach ($this->cycle as $k => $v)
			{
				if ($v == $this->socket)
				{
					if (($accept = socket_accept($v)) < 0)
					{
						continue;
					}
					//如果请求来自监听端口的那个套接字，则创建一个新的套结字用于通信
					$this->add_accept($accept);
					continue;
				}
				$index = array_search($v, $this->accept);
				if ($index === null)
				{
					continue;
				}
				
				if (! @socket_recv($v, $data, 1024, 0) || ! $data)
				{
					//没消息的socket就跳过
					$this->close($v);
					continue;
				}
				
				if (! $this->isHand[$index])
				{
					$this->upgrade($v, $data, $index);
					if (! empty($this->funcs['add']))
					{
						call_user_func_array($this->funcs['add'], array($this));
					}
					continue;
				}
				
				$data = $this->decode($data);
				if (! empty($this->funcs['send']))
				{
					call_user_func_array($this->funcs['send'], array($data, $index, $this));
				}
			}
			
			sleep(1);
			
		}
	}
	
	//增加一个初次链接的用户
	private function add_accept($accept)
	{
		$this->accept[] = $accept;
		$index = array_keys($this->accept);
		$index = end($index);
		$this->isHand[$index] = false;
	}
	
	//关闭一个链接
	private function close($accept)
	{
		$index = array_search($accept, $this->accept);
		socket_close($accept);
		unset($this->accept[$index]);
		unset($this->isHand[$index]);
		if (! empty($this->funcs['close']))
		{
			call_user_func_array($this->funcs['close'], array($this));
		}
	}
	
	//响应升级协议
	private function upgrade($accept, $data, $index)
	{
		if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $data, $match))
		{
			$key = base64_encode(sha1($match[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
			$upgrade = "HTTP/1.1 101 Switching Protocol\r\n"
					 . "Upgrade: websocket\r\n"
					 . "Connection: Upgrade\r\n"
					 . "Sec-WebSocket-Accept: " . $key ."\r\n\r\n";
			socket_write($accept, $upgrade, strlen($upgrade));
			$this->isHand[$index] = true;
		}
	}
	
	public function frame($s)
	{
		$a = str_split($s, 125);
		if (count($a) == 1)
		{
			return "\x81" . chr(strlen($a[0])) . $a[0];
		}
		
		$ns = "";
		foreach ($a as $o)
		{
			$ns .= "\x81" . chr(strlen($o)) . $o;
		}
		
		return $ns;
	}
	
	public function decode($buffer)
	{
		$len = $masks = $data = $decoded = null;
		$len = ord($buffer[1]) & 127;
		if ($len === 126)
		{
			$masks = substr($buffer, 4, 4);
			$data = substr($buffer, 8);
		}
		elseif ($len === 127)
		{
			$masks = substr($buffer, 2, 4);
			$data = substr($buffer, 14);			
		}
		else 
		{
			$masks = substr($buffer, 2, 4);
			$data = substr($buffer, 6);
		}
		
		for ($index = 0; $index < strlen($data); $index++)
		{
			$decoded .= $data[$index] ^ $masks[$index % 4];
		}
		
		return $decoded;
	}
	
	
}
?>