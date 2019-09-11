<?php


namespace CodeMade\WuiBundle\Liquid;


use CodeMade\WuiBundle\Liquid\Liquid;
use CodeMade\WuiBundle\Liquid\Template;
use CodeMade\WuiBundle\Pdo\Logger;
use CodeMade\WuiBundle\Pdo\PdoQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class Panel
{
    private $kernel;
    private $start_time;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var array paramets for template panel
     */
    private $paramets = [];

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->request = $this->kernel->getContainer()->get('request_stack')->getCurrentRequest();
        $this->start_time = microtime(true);

    }


    /**
     * @param Template $liquid
     * @param $start_time
     * @param $content
     * @param $parametrs
     * @return string|string[]|null
     * @throws \CodeMade\WuiBundle\Liquid\LiquidException
     */
    public function render(Template $liquid, $start_time,  $content, $parametrs)
    {
        $this->generateParametrs($start_time, $parametrs);

        $file_panel = __DIR__ .'/../Resources/liquid/panel.tpl';

        if (file_exists($file_panel)) {
            $html = file_get_contents($file_panel);
            $liquid->parse($html);
            $content_panel = $liquid->render($this->paramets);
            $content = preg_replace('%(</body>)%i', $content_panel. '$1', $content);
        }

        return $content;
    }


    /**
     *
     */
    protected function generateParametrs($start_time, $parameters)
    {


        $this->paramets['liquid'] = [
            'locale' => $this->request->getLocale()
        ];

        $this->paramets['router']  = $this->request->attributes->all();

        $this->paramets['router']['status_message'] = 'OK';
        $this->paramets['router']['status'] = '200';
        $this->paramets['router']['class'] = 'sf-toolbar-status-green';

        if(isset($parameters['_error']) && $parameters['_error'] == '404') {
            $this->paramets['router']['status_message'] = 'Not Found';
            $this->paramets['router']['status'] = '404';
            $this->paramets['router']['class'] = 'sf-toolbar-status-red';
        }

        $this->paramets['router']['name'] = basename(str_replace('\\', '/', $this->paramets['router']['_controller']));




        $this->paramets['database']['time'] = Logger::getTimeAll();
        $this->paramets['database']['error'] = Logger::getError();
        $this->paramets['database']['log'] = Logger::getLog();

        $this->paramets['database']['error'] = array_diff( $this->paramets['database']['error'], ['00000','1054', ''] );

        if (count($this->paramets['database']['error']) > 0)
        {
            $this->paramets['database']['class'] = ' sf-toolbar-status-red';
        }


        $symfony_version = \Symfony\Component\HttpKernel\Kernel::VERSION;
        $this->paramets['symfony']['v'] = $symfony_version;
        $this->paramets['php']['v'] = phpversion();
        $this->paramets['symfony']['environment'] = $this->kernel->getEnvironment();

        $start_time_symfony = isset($GLOBALS['StartMicrotime']) ? $GLOBALS['StartMicrotime'] : $this->kernel->getStartTime();
        $time = $GLOBALS['StartMicrotime'] - $this->kernel->getStartTime();


        $this->paramets['time']['all'] = $this->getExecTime($start_time_symfony);
        $this->paramets['time']['liquid'] = $this->getExecTime($start_time);
        $this->paramets['time']['initialization'] = '-';
        if (isset($GLOBALS['ControllerMicrotime'])) {
            $this->paramets['time']['initialization'] = $this->getExecTime($start_time_symfony, $GLOBALS['ControllerMicrotime']);
        }

        $this->paramets['php']['memory_limit'] = str_replace('M', '', ini_get('memory_limit'));
        $this->paramets['php']['memory_usage'] = number_format(((memory_get_usage(true)/1024)/1024), 0);
        $this->paramets['php']['memory_usage_peak'] = number_format(((memory_get_peak_usage(true)/1024)/1024), 0);
        $this->paramets['panel'] = substr(md5('codemade-'.time()), 0, 7);

    }

    public function getExecTime($timeStart = null, $timeFinish = null) {

        $timeFinish = empty($timeFinish) ? microtime(true) : $timeFinish;

        $timeStart = isset($timeStart) ? $timeStart : $GLOBALS['StartMicrotime'];

        $durationInMilliseconds = ($timeFinish - $timeStart) * 1000;
        return number_format($durationInMilliseconds, 0, '.', '');
    }

    public function getTime($time) {
        return number_format($time, 0, '.', '');
    }

    public function obj2array ( &$Instance ) {
        $clone = (array) $Instance;
        $rtn = array ();
        $rtn['___SOURCE_KEYS_'] = $clone;

        while ( list ($key, $value) = each ($clone) ) {
            $aux = explode ("\0", $key);
            $newkey = $aux[count($aux)-1];
            $rtn[$newkey] = &$rtn['___SOURCE_KEYS_'][$key];
        }

        return $rtn;
    }
}