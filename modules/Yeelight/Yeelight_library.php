<?php
//===========Lybrary=====================================================

//===========YeelightClient==============================================
class YeelightClient	
{
    /**
     * @var YeelightRawClient
     */
    private $client;

    /**
     * YeelightClient constructor.
     *
     * @param int $readTimeout
     */
    public function __construct($readTimeout = 1)
    {
        $socketFactory = new Factory();
        $bulbFactory = new BulbFactory($socketFactory);
        	$this->client = new YeelightRawClient(
            $readTimeout,
            $socketFactory->createUdp4(),
            $bulbFactory
        );
    }
    /**
     * @return Bulb[]
     *
     * @throws SocketException
     */
    public function search()
    {
        return $this->client->search();
    }
	public function search_prop()
    {
        return $this->client->search_prop();
    }
}

//=============YeelightRawClient===================================================

class YeelightRawClient
{
    const DISCOVERY_RESPONSE = "M-SEARCH * HTTP/1.1\r\n    
        HOST: 239.255.255.250:1982\r\n                     
        MAN: \"ssdp:discover\"\r\n                         
        ST: wifi_bulb\r\n";
    const MULTICAST_ADDRESS = '239.255.255.250:1982';
    const NO_FLAG = 0;
    const PACKET_LENGTH = 4096;

    /**
     * @var Bulb[]
     */
    private $bulbList = [];
	private $bulbListProp = [];

    /**
     * @var int  
     */
    private $readTimeout;

    /**
     * @@var Socket
     */
    private $socket;

    /**
     * @@var BulbFactory
     */
    private $bulbFactory;

    /**
     * YeelightClient constructor.
     *
     * @param int         $readTimeout
     * @param Socket      $socket
     * @param BulbFactory $bulbFactory
     */
    public function __construct($readTimeout, Socket $socket, BulbFactory $bulbFactory)
    {
        $this->readTimeout = $readTimeout;
        $this->socket = $socket;
        $this->bulbFactory = $bulbFactory;
    }

    /**
     * Local discovery for bulbs
     *
     * @return Bulb[]
     *
     * @throws SocketException
     */
    public function search()
    {
        $this->socket->sendTo(self::DISCOVERY_RESPONSE, self::NO_FLAG, self::MULTICAST_ADDRESS);
        $this->socket->setBlocking(false);
        while ($this->socket->selectRead($this->readTimeout)) {
            $data = $this->formatResponse($this->socket->read(self::PACKET_LENGTH));
            $bulb = $this->bulbFactory->create($data);
            $this->bulbList[$bulb->getIp()] = $bulb;
        }

        return $this->bulbList;
    }
//=========этот блок - переделка под чтение ВСЕХ свойств========================<
    public function search_prop()
    {
        $this->socket->sendTo(self::DISCOVERY_RESPONSE, self::NO_FLAG, self::MULTICAST_ADDRESS);
        $this->socket->setBlocking(false);
        while ($this->socket->selectRead($this->readTimeout)) {
            $data = $this->formatResponse($this->socket->read(self::PACKET_LENGTH));
            $bulb = $this->bulbFactory->create($data);
            $this->bulbList[$bulb->getIp()] = $bulb;
			$this->bulbListProp[$bulb->getIp()] = $data;			
        }        
        
		return $this->bulbListProp;
    }

//===============================================================================>
    /**
     * @param string $data
     *
     * @return array
     */
    private function formatResponse($data)
    {
        return array_reduce(explode("\n", trim($data)), function ($carry, $item) {
            $res = explode(':', $item, 2);
            $carry[trim(reset($res))] = end($res);

            return $carry;
        }, []);
    }
}
//============BulbProperties====================================================
class BulbProperties
{
    const POWER = 'power';
    const BRIGHT = 'bright';
    const COLOR_TEMPERATURE = 'ct';
    const RGB = 'rgb';
    const HUE = 'hue';
    const SATURATION = 'sat';
    const COLOR_MODE = 'color_mode';
    const FLOWING = 'flowing';
    const DELAY_OFF = 'delayoff';
    const FLOW_PARAMS= 'flow_params';
    const MUSIC_ON = 'music_on';
    const NAME = 'name';
}
//=============BulbFactory===================================================
class BulbFactory
{
    const LOCATION = 'Location';
    const ID = 'id';

    /**
     * @var Factory
     */
    private $socketFactory;

    /**
     * BulbFactory constructor.
     *
     * @param Factory $socketFactory
     */
    public function __construct(Factory $socketFactory)
    {
        $this->socketFactory = $socketFactory;
    }

    /**
     * @param array $data
     *
     * @return Bulb
     */
    public function create($data)
    {
        list($ip, $port) = $this->extractIpAndPort($data[self::LOCATION]);

        return new Bulb(
            $this->socketFactory->createTcp4(),
            $ip,
            (int) $port,
            trim($data[self::ID])
        );
    }
    
    /**
     * @param string $location
     *
     * @return array
     */
    private function extractIpAndPort($location)
    {
        $address = explode('yeelight://', $location);
        return explode(':', end($address));
    }
}

//=========Bulb=======================================================
class Bulb
{
    const PACKET_LENGTH = 4096;
    const NO_FLAG = 0;

    const EFFECT_SUDDEN = 'sudden';
    const EFFECT_SMOOTH = 'smooth';
    const ON = 'on';
    const OFF = 'off';
    const ACTION_BEFORE = 0;
    const ACTION_AFTER = 1;
    const ACTION_TURN_OFF = 2;
    const ADJUST_ACTION_INCREASE = 'increase';
    const ADJUST_ACTION_DECREASE = 'decrease';
    const ADJUST_ACTION_CIRCLE = 'circle';
    const ADJUST_PROP_BRIGHTNESS = 'bright';
    const ADJUST_PROP_COLOR_TEMP = 'ct';
    const ADJUST_PROP_COLOR = 'color';

    /**
     * @var Socket
     */
    private $socket;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $id;

    /**
     * Bulb constructor
     *
     * @param Socket $socket
     * @param string $ip
     * @param int    $port
     * @param string $id
     */
    public function __construct(Socket $socket, $ip, $port, $id)
    {
        $this->socket = $socket;
        $this->ip = $ip;
        $this->port = $port;
        $this->id = $id;

        $this->socket->connect($this->getAddress());
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return sprintf('%s:%d', $this->getIp(), $this->getPort());
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Метод используется для получения текущих свойств smart LED
     *
     * @param array $properties Параметр представляет собой список имен свойств (констант из BulbProperties), 
	 *                          ответ содержит список соответствующих значений свойств. Если запрошенное имя свойства 
	 *                          не признается smart LED, то возвращается значение пустой строки ("").	 
     *
     * @return Promise
     */
    public function getProp($properties)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'get_prop',
            'params' => $properties,
        ];
        $this->send($data);
        return $this->read();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param array $data
     */
    private function send($data)
    {
        $data = json_encode($data) . "\r\n";
        $this->socket->send($data, self::NO_FLAG);        
    }

   	private function read()
    {
        $resp =  json_decode($this->socket->read(self::PACKET_LENGTH), true); 
        return $this->read = $resp;
        //$response = new Response(
        //        json_decode($this->socket->read(self::PACKET_LENGTH), true)
        //    );
        //$this-> Response->getDeviceId();
        //$this-> response->getResult();        
	}
	
	/**
     * @return Promise
     */
    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	/**	
	*private function read(): Promise
    *{
    *    return new Promise(function (callable $resolve, callable $reject) {
    *        $response = new Response(
    *            json_decode($this->socket->read(self::PACKET_LENGTH), true)
    *        );
	*
	*            if ($response->isSuccess()) {
	*                $resolve($response);
	*
	*                return;
	*            }
	*            $reject($response->getException());
	*        });
    *}
	*/
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
    /**
     * Этот метод используется для изменения цветовой температуры smart LED
     *
     * @param int    $ctValue  целевая цветовая температура
     * @param string $effect   поддерживает два значения: "sudden" (Bulb::EFFECT_SUDDEN) and "smooth" (Bulb::EFFECT_SMOOTH)
     * @param int    $duration общая длительность изменения "smooth". Измеряется в milliseconds
     *
     * @return Promise
     */
    public function setCtAbx($ctValue, $effect, $duration)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'set_ct_abx',
            'params' => [
                $ctValue,
                $effect,
                $duration,
            ],
        ];
        $this->send($data);

        return $this->read();
    }

    /**
     * Метод используется для изменения цвета (color) smart LED 
     *
     * @param int    $rgbValue целевой цвет, тип integer. Выражается в десятичном целом числе в диапазоне от
     *                         0 до 16777215 (hex: 0xFFFFFF).
     * @param string $effect   поддерживает два значения: "sudden" (Bulb::EFFECT_SUDDEN) и "smooth" (Bulb::EFFECT_SMOOTH)
     * @param int    $duration общее время изменения значения "smooth". Единица измерения - milliseconds.
     *
     * @return Promise
     */
    public function setRgb($rgbValue, $effect, $duration)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'set_rgb',
            'params' => [
                $rgbValue,
                $effect,
                $duration,
            ],
        ];
        $this->send($data);

        return $this->read();
    }

    /**
     * Метод используется для изменения оттенка и насыщенности цвета smart LED
     *
     * @param int    $hue      целевое значение оттенка
     * @param int    $sat      целевое значение насыщенности
     * @param string $effect   поддерживает два значения: "sudden"(быстро) (Bulb::EFFECT_SUDDEN) and "smooth" (плавно) (Bulb::EFFECT_SMOOTH)
     * @param int    $duration общее время изменения значения "smooth". Единица измерения - milliseconds.
     *
     * @return Promise
     */
    public function setHsv($hue, $sat, $effect, $duration)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'set_hsv',
            'params' => [
                $hue,
                $sat,
                $effect,
                $duration,
            ],
        ];
        $this->send($data);

        return $this->read();
    }

    /**
     * Далее методы аналогично руководству
	 * This method is used to change the brightness of a smart LED (яркость)
     *
     * @param int    $brightness is the target brightness. The type is integer and ranges from 1 to 100. The brightness
     *                           is a percentage instead of a absolute value.
     * @param string $effect     support two values: "sudden" (Bulb::EFFECT_SUDDEN) and "smooth" (Bulb::EFFECT_SMOOTH)
     * @param int    $duration   specifies the total time of the gradual changing. The unit is milliseconds
     *
     * @return Promise
     */
    public function setBright($brightness, $effect, $duration)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'set_bright',
            'params' => [
                $brightness,
                $effect,
                $duration,
            ],
        ];
        $this->send($data);

        return $this->read();
    }

    /**
     * This method is used to switch on or off the smart LED (software managed on/off) (включение-выключение)
     *
     * @param string $power    can only be "on" or "off". "on" means turn on the smart LED, "off" means turn off the
     *                         smart LED
     * @param string $effect   support two values: "sudden" (Bulb::EFFECT_SUDDEN) and "smooth" (Bulb::EFFECT_SMOOTH)
     * @param int    $duration specifies the total time of the gradual changing. The unit is milliseconds
     *
     * @return Promise
     */
    public function setPower($power, $effect, $duration)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'set_power',
            'params' => [
                $power,
                $effect,
                $duration,
            ],
        ];
        $this->send($data);

        return $this->read();
    }

    /**
     * This method is used to toggle the smart LED (переключение)
     *
     * @return Promise
     */
    public function toggle()
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'toggle',
            'params' => [],
        ];
        $this->send($data);

        return $this->read();
    }

    /**
     * This method is used to save current state of smart LED in persistent memory. So if user powers off and then
     * powers on the smart LED again (hard power reset), the smart LED will show last saved state
     * сохранение текущего состояния в качестве "по умолчанию"
     * @return Promise
     */
    public function setDefault()
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'set_default',
            'params' => [],
        ];
        $this->send($data);

        return $this->read();
    }

    /**
     * This method is used to start a color flow. Color flow is a series of smart LED visible state changing. It can be
     * brightness changing, color changing or color temperature changing
     * цветовые потоки (см.руководство)
     * @param int   $count              is the total number of visible state changing before color flow stopped. 0
     *                                  means infinite loop on the state changing
     * @param int   $action             is the action taken after the flow is stopped
     *                                  0 means smart LED recover to the state before the color flow started
     *                                  1 means smart LED stay at the state when the flow is stopped
     *                                  2 means turn off the smart LED after the flow is stopped
     * @param array $flowExpression     is the expression of the state changing series in format
     *                                  [
     *                                  [duration, mode, value, brightness],
     *                                  [duration, mode, value, brightness]
     *                                  ]
     *
     * @return Promise
     */
    public function startCf($count, $action, $flowExpression)
    {
        $state = implode(",", array_map(function ($item) {
            return implode(",", $item);
        }, $flowExpression));
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'start_cf',
            'params' => [
                $count,
                $action,
                $state,
            ],
        ];
        $this->send($data);

        return $this->read();
    }

    /**
     * This method is used to stop a running color flow
     * остановка текущего цветового потока
     * @return Promise
     */
    public function stopCf()
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'stop_cf',
            'params' => [],
        ];
        $this->send($data);

        return $this->read();
    }

    /**
     * This method is used to set the smart LED directly to specified state. If the smart LED is off, then it will turn
     * on the smart LED firstly and then apply the specified command
     * установка сцены (см.руководство)
     * @param array $params array that firs element is a class (color, hsv, ct, cf, auto_dealy_off) and next 3 are
     *                      class specific eg.
     *                      ['color', 65280, 70]
     *                      ['hsv', 300, 70, 100]
     *                      ['ct', 5400, 100]
     *                      ['cf',0,0,"500,1,255,100,1000,1,16776960,70"]
     *                      ['auto_delay_off', 50, 5]
     *
     * @return Promise
     */
    public function setScene($params)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'set_scene',
            'params' => $params,
        ];
        $this->send($data);

        return $this->read();
    }

    /**
     * This method is used to start a timer job on the smart LED
     * запуск таймера
     * @param int $type  type of the cron job
     * @param int $value length of the timer (in minutes)
     *
     * @return Promise
     */
    public function cronAdd($type, $value)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'cron_add',
            'params' => [
                $type,
                $value,
            ],
        ];
        $this->send($data);

        return $this->read();
    }

    /**
     * This method is used to retrieve the setting of the current cron job of the specified type
     * получение значений таймера
     * @param int $type type of the cron job
     *
     * @return Promise
     */
    public function cronGet($type)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'cron_get',
            'params' => [
                $type,
            ],
        ];
        $this->send($data);

        return $this->read();
    }

    /**
     * This method is used to stop the specified cron job
     * остановка таймера
     * @param int $type type of the cron job
     *
     * @return Promise
     */
    public function cronDel($type)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'cron_del',
            'params' => [
                $type,
            ],
        ];
        $this->send($data);

        return $this->read();
    }

    /**
     * This method is used to change brightness, CT or color of a smart LED without knowing the current value, it's
     * main used by controllers.
     * относительная корректировка значений (яркость, цвето, оттенок) smart LED
     * @param string $action the direction of the adjustment The valid value can be:
     *                       “increase": increase the specified property (Bulb::ADJUST_ACTION_INCREASE)
     *                       “decrease": decrease the specified property (Bulb::ADJUST_ACTION_DECREASE)
     *                       “circle": increase the specified property, after it reaches the max
     *                       (Bulb::ADJUST_ACTION_CIRCLE)
     * @param string $prop   the property to adjust. The valid value can be:
     *                       “bright": adjust brightness (Bulb::ADJUST_PROP_BRIGHTNESS)
     *                       “ct": adjust color temperature (Bulb::ADJUST_PROP_COLOR_TEMP)
     *                       “color": adjust color. (Bulb::ADJUST_PROP_COLOR) (When “prop" is “color", the “action" can
     *                       only be “circle", otherwise, it will be deemed as invalid request.)
     *
     * @return Promise
     */
    public function setAdjust($action, $prop)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'set_adjust',
            'params' => [
                $action,
                $prop,
            ],
        ];
        $this->send($data);

        return $this->read();
    }

    /**
     * This method is used to start or stop music mode on a device
     * запуск музыкального режима
     * @param int         $action the action of set_music command
     * @param string|null $host   the IP address of the music server
     * @param int|null    $port   the TCP port music application is listening on
     *
     * @return Promise
     */
    public function setMusic($action, $host = null, $port = null)
    {
        $params = [
            $action,
        ];

        if (!is_null($host)) {
            $params[] = $host;
        }

        if (!is_null($port)) {
            $params[] = $port;
        }

        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'set_music',
            'params' => $params,
        ];
        $this->send($data);

        return $this->read();
    }

    /**
     * This method is used to name the device
     * переименование smart LED
     * @param string $name name of the device
     *
     * @return Promise
     */
    public function setName($name)
    {
        $data = [
            'id' => hexdec($this->getId()),
            'method' => 'set_name',
            'params' => [
                $name,
            ],
        ];
        $this->send($data);

        return $this->read();
    }
}

//================================================================

//==============add lybrary========================================	

//=============Socket==============================================
class Socket
{
    /**
     * reference to actual socket resource
     *
     * @var resource
     */
    private $resource;

    /**
     * instanciate socket wrapper for given socket resource
     *
     * should usually not be called manually, see Factory
     *
     * @param resource $resource
     * @see Factory as the preferred (and simplest) way to construct socket instances
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    /**
     * get actual socket resource
     *
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * accept an incomming connection on this listening socket
     *
     * @return \Socket\Raw\Socket new connected socket used for communication
     * @throws Exception on error, if this is not a listening socket or there's no connection pending
     * @see self::selectRead() to check if this listening socket can accept()
     * @see Factory::createServer() to create a listening socket
     * @see self::listen() has to be called first
     * @uses socket_accept()
     */
    public function accept()
    {
        $resource = @socket_accept($this->resource);
        if ($resource === false) {
            throw Exception::createFromGlobalSocketOperation();
        }
        return new Socket($resource);
    }

    /**
     * binds a name/address/path to this socket
     *
     * has to be called before issuing connect() or listen()
     *
     * @param string $address either of IPv4:port, hostname:port, [IPv6]:port, unix-path
     * @return self $this (chainable)
     * @throws Exception on error
     * @uses socket_bind()
     */
    public function bind($address)
    {
        $ret = @socket_bind($this->resource, $this->unformatAddress($address, $port), $port);
        if ($ret === false) {
            throw Exception::createFromSocketResource($this->resource);
        }
        return $this;
    }

    /**
     * close this socket
     *
     * ATTENTION: make sure to NOT re-use this socket instance after closing it!
     * its socket resource remains closed and most further operations will fail!
     *
     * @return self $this (chainable)
     * @see self::shutdown() should be called before closing socket
     * @uses socket_close()
     */
    public function close()
    {
        socket_close($this->resource);
        return $this;
    }

    /**
     * initiate a connection to given address
     *
     * @param string $address either of IPv4:port, hostname:port, [IPv6]:port, unix-path
     * @return self $this (chainable)
     * @throws Exception on error
     * @uses socket_connect()
     */
    public function connect($address)
    {
        $ret = @socket_connect($this->resource, $this->unformatAddress($address, $port), $port);
        if ($ret === false) {
            throw Exception::createFromSocketResource($this->resource);
        }
        return $this;
    }

    /**
     * Initiates a new connection to given address, wait for up to $timeout seconds
     *
     * The given $timeout parameter is an upper bound, a maximum time to wait
     * for the connection to be either accepted or rejected.
     *
     * The resulting socket resource will be set to non-blocking mode,
     * regardless of its previous state and whether this method succedes or
     * if it fails. Make sure to reset with `setBlocking(true)` if you want to
     * continue using blocking calls.
     *
     * @param string $address either of IPv4:port, hostname:port, [IPv6]:port, unix-path
     * @param float  $timeout maximum time to wait (in seconds)
     * @return self $this (chainable)
     * @throws Exception on error
     * @uses self::setBlocking() to enable non-blocking mode
     * @uses self::connect() to initiate the connection
     * @uses self::selectWrite() to wait for the connection to complete
     * @uses self::assertAlive() to check connection state
     */
    public function connectTimeout($address, $timeout)
    {
        $this->setBlocking(false);

        try {
            // socket is non-blocking, so connect should emit EINPROGRESS
            $this->connect($address);

            // socket is already connected immediately?
            return $this;
        }
        catch (Exception $e) {
            // non-blocking connect() should be EINPROGRESS => otherwise re-throw
            if ($e->getCode() !== SOCKET_EINPROGRESS) {
                throw $e;
            }

            // connection should be completed (or rejected) within timeout
            if ($this->selectWrite($timeout) === false) {
                throw new Exception('Timed out while waiting for connection', SOCKET_ETIMEDOUT);
            }

            // confirm connection success (or fail if connected has been rejected)
            $this->assertAlive();

            return $this;
        }
    }

    /**
     * get socket option
     *
     * @param int $level
     * @param int $optname
     * @return mixed
     * @throws Exception on error
     * @uses socket_get_option()
     */
    public function getOption($level, $optname)
    {
        $value = @socket_get_option($this->resource, $level, $optname);
        if ($value === false) {
            throw Exception::createFromSocketResource($this->resource);
        }
        return $value;
    }

    /**
     * get remote side's address/path
     *
     * @return string
     * @throws Exception on error
     * @uses socket_getpeername()
     */
    public function getPeerName()
    {
        $ret = @socket_getpeername($this->resource, $address, $port);
        if ($ret === false) {
            throw Exception::createFromSocketResource($this->resource);
        }
        return $this->formatAddress($address, $port);
    }

    /**
     * get local side's address/path
     *
     * @return string
     * @throws Exception on error
     * @uses socket_getsockname()
     */
    public function getSockName()
    {
        $ret = @socket_getsockname($this->resource, $address, $port);
        if ($ret === false) {
            throw Exception::createFromSocketResource($this->resource);
        }
        return $this->formatAddress($address, $port);
    }

    /**
     * start listen for incoming connections
     *
     * @param int $backlog maximum number of incoming connections to be queued
     * @return self $this (chainable)
     * @throws Exception on error
     * @see self::bind() has to be called first to bind name to socket
     * @uses socket_listen()
     */
    public function listen($backlog = 0)
    {
        $ret = @socket_listen($this->resource, $backlog);
        if ($ret === false) {
            throw Exception::createFromSocketResource($this->resource);
        }
        return $this;
    }

    /**
     * read up to $length bytes from connect()ed / accept()ed socket
     *
     * The $type parameter specifies if this should use either binary safe reading
     * (PHP_BINARY_READ, the default) or stop at CR or LF characters (PHP_NORMAL_READ)
     *
     * @param int $length maximum length to read
     * @param int $type   either of PHP_BINARY_READ (the default) or PHP_NORMAL_READ
     * @return string
     * @throws Exception on error
     * @see self::recv() if you need to pass flags
     * @uses socket_read()
     */
    public function read($length, $type = PHP_BINARY_READ)
    {
        $data = @socket_read($this->resource, $length, $type);
        if ($data === false) {
            throw Exception::createFromSocketResource($this->resource);
        }
        return $data;
    }

    /**
     * receive up to $length bytes from connect()ed / accept()ed socket
     *
     * @param int $length maximum length to read
     * @param int $flags
     * @return string
     * @throws Exception on error
     * @see self::read() if you do not need to pass $flags
     * @see self::recvFrom() if your socket is not connect()ed
     * @uses socket_recv()
     */
    public function recv($length, $flags)
    {
        $ret = @socket_recv($this->resource, $buffer, $length, $flags);
        if ($ret === false) {
            throw Exception::createFromSocketResource($this->resource);
        }
        return $buffer;
    }

    /**
     * receive up to $length bytes from socket
     *
     * @param int    $length maximum length to read
     * @param int    $flags
     * @param string $remote reference will be filled with remote/peer address/path
     * @return string
     * @throws Exception on error
     * @see self::recv() if your socket is connect()ed
     * @uses socket_recvfrom()
     */
    public function recvFrom($length, $flags, &$remote)
    {
        $ret = @socket_recvfrom($this->resource, $buffer, $length, $flags, $address, $port);
        if ($ret === false) {
            throw Exception::createFromSocketResource($this->resource);
        }
        $remote = $this->formatAddress($address, $port);
        return $buffer;
    }

    /**
     * check socket to see if a read/recv/revFrom will not block
     *
     * @param float|NULL $sec maximum time to wait (in seconds), 0 = immediate polling, null = no limit
     * @return boolean true = socket ready (read will not block), false = timeout expired, socket is not ready
     * @throws Exception on error
     * @uses socket_select()
     */
    public function selectRead($sec = 0)
    {
        $usec = $sec === null ? null : (($sec - floor($sec)) * 1000000);
        $r = array($this->resource);
        $ret = @socket_select($r, $x, $x, $sec, $usec);
        if ($ret === false) {
            throw Exception::createFromGlobalSocketOperation('Failed to select socket for reading');
        }
        return !!$ret;
    }

    /**
     * check socket to see if a write/send/sendTo will not block
     *
     * @param float|NULL $sec maximum time to wait (in seconds), 0 = immediate polling, null = no limit
     * @return boolean true = socket ready (write will not block), false = timeout expired, socket is not ready
     * @throws Exception on error
     * @uses socket_select()
     */
    public function selectWrite($sec = 0)
    {
        $usec = $sec === null ? null : (($sec - floor($sec)) * 1000000);
        $w = array($this->resource);
        $ret = @socket_select($x, $w, $x, $sec, $usec);
        if ($ret === false) {
            throw Exception::createFromGlobalSocketOperation('Failed to select socket for writing');
        }
        return !!$ret;
    }

    /**
     * send given $buffer to connect()ed / accept()ed socket
     *
     * @param string $buffer
     * @param int    $flags
     * @return int number of bytes actually written (make sure to check against given buffer length!)
     * @throws Exception on error
     * @see self::write() if you do not need to pass $flags
     * @see self::sendTo() if your socket is not connect()ed
     * @uses socket_send()
     */
    public function send($buffer, $flags)
    {
        $ret = @socket_send($this->resource, $buffer, strlen($buffer), $flags);
        if ($ret === false) {
            throw Exception::createFromSocketResource($this->resource);
        }
        return $ret;
    }

    /**
     * send given $buffer to socket
     *
     * @param string $buffer
     * @param int    $flags
     * @param string $remote remote/peer address/path
     * @return int number of bytes actually written
     * @throws Exception on error
     * @see self::send() if your socket is connect()ed
     * @uses socket_sendto()
     */
    public function sendTo($buffer, $flags, $remote)
    {
        $ret = @socket_sendto($this->resource, $buffer, strlen($buffer), $flags, $this->unformatAddress($remote, $port), $port);
        if ($ret === false) {
            throw Exception::createFromSocketResource($this->resource);
        }
        return $ret;
    }

    /**
     * enable/disable blocking/nonblocking mode (O_NONBLOCK flag)
     *
     * @param boolean $toggle
     * @return self $this (chainable)
     * @throws Exception on error
     * @uses socket_set_block()
     * @uses socket_set_nonblock()
     */
    public function setBlocking($toggle = true)
    {
        $ret = $toggle ? @socket_set_block($this->resource) : @socket_set_nonblock($this->resource);
        if ($ret === false) {
            throw Exception::createFromSocketResource($this->resource);
        }
        return $this;
    }

    /**
     * set socket option
     *
     * @param int   $level
     * @param int   $optname
     * @param mixed $optval
     * @return self $this (chainable)
     * @throws Exception on error
     * @see self::getOption()
     * @uses socket_set_option()
     */
    public function setOption($level, $optname, $optval)
    {
        $ret = @socket_set_option($this->resource, $level, $optname, $optval);
        if ($ret === false) {
            throw Exception::createFromSocketResource($this->resource);
        }
        return $this;
    }

    /**
     * shuts down socket for receiving, sending or both
     *
     * @param int $how 0 = shutdown reading, 1 = shutdown writing, 2 = shutdown reading and writing
     * @return self $this (chainable)
     * @throws Exception on error
     * @see self::close()
     * @uses socket_shutdown()
     */
    public function shutdown($how = 2)
    {
        $ret = @socket_shutdown($this->resource, $how);
        if ($ret === false) {
            throw Exception::createFromSocketResource($this->resource);
        }
        return $this;
    }

    /**
     * write $buffer to connect()ed / accept()ed socket
     *
     * @param string $buffer
     * @return int number of bytes actually written
     * @throws Exception on error
     * @see self::send() if you need to pass flags
     * @uses socket_write()
     */
    public function write($buffer)
    {
        $ret = @socket_write($this->resource, $buffer);
        if ($ret === false) {
            throw Exception::createFromSocketResource($this->resource);
        }
        return $ret;
    }

    /**
     * get socket type as passed to socket_create()
     *
     * @return int usually either SOCK_STREAM or SOCK_DGRAM
     * @throws Exception on error
     * @uses self::getOption()
     */
    public function getType()
    {
        return $this->getOption(SOL_SOCKET, SO_TYPE);
    }

    /**
     * assert that this socket is alive and its error code is 0
     *
     * This will fetch and reset the current socket error code from the
     * socket and options and will throw an Exception along with error
     * message and code if the code is not 0, i.e. if it does indicate
     * an error situation.
     *
     * Calling this method should not be needed in most cases and is
     * likely to not throw an Exception. Each socket operation like
     * connect(), send(), etc. will throw a dedicated Exception in case
     * of an error anyway.
     *
     * @return self $this (chainable)
     * @throws Exception if error code is not 0
     * @uses self::getOption() to retrieve and clear current error code
     * @uses self::getErrorMessage() to translate error code to
     */
    public function assertAlive()
    {
        $code = $this->getOption(SOL_SOCKET, SO_ERROR);
        if ($code !== 0) {
            throw Exception::createFromCode($code, 'Socket error');
        }
        return $this;
    }

    /**
     * format given address/host/path and port
     *
     * @param string $address
     * @param int    $port
     * @return string
     */
    protected function formatAddress($address, $port)
    {
        if ($port !== 0) {
            if (strpos($address, ':') !== false) {
                $address = '[' . $address . ']';
            }
            $address .= ':' . $port;
        }
        return $address;
    }

    /**
     * format given address by splitting it into returned address and port set by reference
     *
     * @param string $address
     * @param int $port
     * @return string address with port removed
     */
    protected function unformatAddress($address, &$port)
    {
        // [::1]:2 => ::1 2
        // test:2 => test 2
        // ::1 => ::1
        // test => test

        $colon = strrpos($address, ':');

        // there is a colon and this is the only colon or there's a closing IPv6 bracket right before it
        if ($colon !== false && (strpos($address, ':') === $colon || strpos($address, ']') === ($colon - 1))) {
            $port = (int)substr($address, $colon + 1);
            $address = substr($address, 0, $colon);

            // remove IPv6 square brackets
            if (substr($address, 0, 1) === '[') {
                $address = substr($address, 1, -1);
            }
        }
        return $address;
    }
}

//=============Factory===================================================
class Factory
{
    public function createClient($address)
    {
        $socket = $this->createFromString($address, $scheme);

        try {
            $socket->connect($address);
        }
        catch (Exception $e) {
            $socket->close();
            throw $e;
        }

        return $socket;
    }

    /**
     * create server socket bound to given address (and start listening for streaming clients to connect to this stream socket)
     *
     * @param string $address address to bind socket to
     * @return \Socket\Raw\Socket
     * @throws Exception on error
     * @uses self::createFromString()
     * @uses Socket::bind()
     * @uses Socket::listen() only for stream sockets (TCP/UNIX)
     */
    public function createServer($address)
    {
        $socket = $this->createFromString($address, $scheme);

        try {
            $socket->bind($address);

            if ($socket->getType() === SOCK_STREAM) {
                $socket->listen();
            }
        }
        catch (Exception $e) {
            $socket->close();
            throw $e;
        }

        return $socket;
    }

    /**
     * create TCP/IPv4 stream socket
     *
     * @return \Socket\Raw\Socket
     * @throws Exception on error
     * @uses self::create()
     */
    public function createTcp4()
    {
        return $this->create(AF_INET, SOCK_STREAM, SOL_TCP);
    }

    /**
     * create TCP/IPv6 stream socket
     *
     * @return \Socket\Raw\Socket
     * @throws Exception on error
     * @uses self::create()
     */
    public function createTcp6()
    {
        return $this->create(AF_INET6, SOCK_STREAM, SOL_TCP);
    }

    /**
     * create UDP/IPv4 datagram socket
     *
     * @return \Socket\Raw\Socket
     * @throws Exception on error
     * @uses self::create()
     */
    public function createUdp4()
    {
        return $this->create(AF_INET, SOCK_DGRAM, SOL_UDP);
    }

    /**
     * create UDP/IPv6 datagram socket
     *
     * @return \Socket\Raw\Socket
     * @throws Exception on error
     * @uses self::create()
     */
    public function createUdp6()
    {
        return $this->create(AF_INET6, SOCK_DGRAM, SOL_UDP);
    }

    /**
     * create local UNIX stream socket
     *
     * @return \Socket\Raw\Socket
     * @throws Exception on error
     * @uses self::create()
     */
    public function createUnix()
    {
        return $this->create(AF_UNIX, SOCK_STREAM, 0);
    }

    /**
     * create local UNIX datagram socket (UDG)
     *
     * @return \Socket\Raw\Socket
     * @throws Exception on error
     * @uses self::create()
     */
    public function createUdg()
    {
        return $this->create(AF_UNIX, SOCK_DGRAM, 0);
    }

    /**
     * create raw ICMP/IPv4 datagram socket (requires root!)
     *
     * @return \Socket\Raw\Socket
     * @throws Exception on error
     * @uses self::create()
     */
    public function createIcmp4()
    {
        return $this->create(AF_INET, SOCK_RAW, getprotobyname('icmp'));
    }

    /**
     * create raw ICMPv6 (IPv6) datagram socket (requires root!)
     *
     * @return \Socket\Raw\Socket
     * @throws Exception on error
     * @uses self::create()
     */
    public function createIcmp6()
    {
        return $this->create(AF_INET6, SOCK_RAW, 58 /*getprotobyname('icmp')*/);
    }

    /**
     * create low level socket with given arguments
     *
     * @param int $domain
     * @param int $type
     * @param int $protocol
     * @return \Socket\Raw\Socket
     * @throws Exception if creating socket fails
     * @uses socket_create()
     */
    public function create($domain, $type, $protocol)
    {
        $sock = @socket_create($domain, $type, $protocol);
        if ($sock === false) {
            throw Exception::createFromGlobalSocketOperation('Unable to create socket');
        }
        return new Socket($sock);
    }

    /**
     * create a pair of indistinguishable sockets (commonly used in IPC)
     *
     * @param int $domain
     * @param int $type
     * @param int $protocol
     * @return \Socket\Raw\Socket[]
     * @throws Exception if creating pair of sockets fails
     * @uses socket_create_pair()
     */
    public function createPair($domain, $type, $protocol)
    {
        $ret = @socket_create_pair($domain, $type, $protocol, $pair);
        if ($ret === false) {
            throw Exception::createFromGlobalSocketOperation('Unable to create pair of sockets');
        }
        return array(new Socket($pair[0]), new Socket($pair[1]));
    }

    /**
     * create TCP/IPv4 stream socket and listen for new connections
     *
     * @param int $port
     * @param int $backlog
     * @return \Socket\Raw\Socket
     * @throws Exception if creating listening socket fails
     * @uses socket_create_listen()
     * @see self::createServer() as an alternative to bind to specific IP, IPv6, UDP, UNIX, UGP
     */
    public function createListen($port, $backlog = 128)
    {
        $sock = @socket_create_listen($port, $backlog);
        if ($sock === false) {
            throw Exception::createFromGlobalSocketOperation('Unable to create listening socket');
        }
        return new Socket($sock);
    }

    /**
     * create socket for given address
     *
     * @param string $address (passed by reference in order to remove scheme, if present)
     * @param string $scheme  default scheme to use, defaults to TCP (passed by reference in order to update with actual scheme used)
     * @return \Socket\Raw\Socket
     * @throws InvalidArgumentException if given address is invalid
     * @throws Exception in case creating socket failed
     * @uses self::createTcp4() etc.
     */
    public function createFromString(&$address, &$scheme)
    {
        if ($scheme === null) {
            $scheme = 'tcp';
        }

        $hasScheme = false;

        $pos = strpos($address, '://');
        if ($pos !== false) {
            $scheme = substr($address, 0, $pos);
            $address = substr($address, $pos + 3);
            $hasScheme = true;
        }

        if (strpos($address, ':') !== strrpos($address, ':') && in_array($scheme, array('tcp', 'udp', 'icmp'))) {
            // TCP/UDP/ICMP address with several colons => must be IPv6
            $scheme .= '6';
        }

        if ($scheme === 'tcp') {
            $socket = $this->createTcp4();
        } elseif ($scheme === 'udp') {
            $socket = $this->createUdp4();
        } elseif ($scheme === 'tcp6') {
            $socket = $this->createTcp6();
        } elseif ($scheme === 'udp6') {
            $socket = $this->createUdp6();
        } elseif ($scheme === 'unix') {
            $socket = $this->createUnix();
        } elseif ($scheme === 'udg') {
            $socket = $this->createUdg();
        } elseif ($scheme === 'icmp') {
            $socket = $this->createIcmp4();
        } elseif ($scheme === 'icmp6') {
            $socket = $this->createIcmp6();
            if ($hasScheme) {
                // scheme was stripped from address, resulting IPv6 must not
                // have a port (due to ICMP) and thus must not be enclosed in
                // square brackets
                $address = trim($address, '[]');
            }
        } else {
            throw new InvalidArgumentException('Invalid address scheme given');
        }
        return $socket;
    }
}
//================================================================
//class SocketException extends \Exception
//{
//}
//class BulbCreateException extends Exception
//{
//}
//class Exception extends \Exception
//{
//}
//===========Response (обработка ответа от устройства - нафиг, мне проще в запросе обработать)============================
class Response
{
    /**
     * @var int
     */
    private $deviceId;

    /**
     * @var array
     */
    private $result = [];

    /**
     * @var BulbCommandException|null
     */
    private $exception = null;

    /**
     * Response constructor.
     *
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->deviceId = $response['id'];
        if (isset($response['error'])) {
            $this->exception = new BulbCommandException(
                $response['error']['message'],
                $response['error']['code'],
                $response['id']
            );
        } else {
            $this->result = $response['result'];
        }
    }

    /**
     * @return int
     */
    public function getDeviceId()
    {
        return $this->deviceId;
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return null|BulbCommandException
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return is_null($this->exception);
    }
}
//========BulbCommandException (обработка исключений)========================================================
class BulbCommandException extends Exception
{
    /**
     * @var int
     */
    private $bulbId;

    /**
     * BulbCommandException constructor.
     *
     * @param string $message
     * @param int    $code
     * @param int    $bulbId
     */
    public function __construct(string $message, int $code, int $bulbId)
    {
        parent::__construct($message, $code);
        $this->bulbId = $bulbId;
    }

    /**
     * @return int
     */
    public function getBulbId()
    {
        return $this->bulbId;
    }
}

//========LybraryEnd========================================================
