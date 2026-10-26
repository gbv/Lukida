<?php




















namespace PHPMailer;








class DSNConfigurator
{
    







    public static function mailer($dsn, $exceptions = null)
    {
        static $configurator = null;

        if (null === $configurator) {
            $configurator = new DSNConfigurator();
        }

        return $configurator->configure(new PHPMailer($exceptions), $dsn);
    }

    







    public function configure(PHPMailer $mailer, $dsn)
    {
        $config = $this->parseDSN($dsn);

        $this->applyConfig($mailer, $config);

        return $mailer;
    }

    








    private function parseDSN($dsn)
    {
        $config = $this->parseUrl($dsn);

        if (false === $config || !isset($config['scheme']) || !isset($config['host'])) {
            throw new Exception('Malformed DSN');
        }

        if (isset($config['query'])) {
            parse_str($config['query'], $config['query']);
        }

        return $config;
    }

    







    private function applyConfig(PHPMailer $mailer, $config)
    {
        switch ($config['scheme']) {
            case 'mail':
                $mailer->isMail();
                break;
            case 'sendmail':
                $mailer->isSendmail();
                break;
            case 'qmail':
                $mailer->isQmail();
                break;
            case 'smtp':
            case 'smtps':
                $mailer->isSMTP();
                $this->configureSMTP($mailer, $config);
                break;
            default:
                throw new Exception(
                    sprintf(
                        'Invalid scheme: "%s". Allowed values: "mail", "sendmail", "qmail", "smtp", "smtps".',
                        $config['scheme']
                    )
                );
        }

        if (isset($config['query'])) {
            $this->configureOptions($mailer, $config['query']);
        }
    }

    





    private function configureSMTP($mailer, $config)
    {
        $isSMTPS = 'smtps' === $config['scheme'];

        if ($isSMTPS) {
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mailer->Host = $config['host'];

        if (isset($config['port'])) {
            $mailer->Port = $config['port'];
        } elseif ($isSMTPS) {
            $mailer->Port = SMTP::DEFAULT_SECURE_PORT;
        }

        $mailer->SMTPAuth = isset($config['user']) || isset($config['pass']);

        if (isset($config['user'])) {
            $mailer->Username = $config['user'];
        }

        if (isset($config['pass'])) {
            $mailer->Password = $config['pass'];
        }
    }

    







    private function configureOptions(PHPMailer $mailer, $options)
    {
        $allowedOptions = get_object_vars($mailer);

        unset($allowedOptions['Mailer']);
        unset($allowedOptions['SMTPAuth']);
        unset($allowedOptions['Username']);
        unset($allowedOptions['Password']);
        unset($allowedOptions['Hostname']);
        unset($allowedOptions['Port']);
        unset($allowedOptions['ErrorInfo']);

        $allowedOptions = \array_keys($allowedOptions);

        foreach ($options as $key => $value) {
            if (!in_array($key, $allowedOptions)) {
                throw new Exception(
                    sprintf(
                        'Unknown option: "%s". Allowed values: "%s"',
                        $key,
                        implode('", "', $allowedOptions)
                    )
                );
            }

            switch ($key) {
                case 'AllowEmpty':
                case 'SMTPAutoTLS':
                case 'SMTPKeepAlive':
                case 'SingleTo':
                case 'UseSendmailOptions':
                case 'do_verp':
                case 'DKIM_copyHeaderFields':
                    $mailer->$key = (bool) $value;
                    break;
                case 'Priority':
                case 'SMTPDebug':
                case 'WordWrap':
                    $mailer->$key = (int) $value;
                    break;
                default:
                    $mailer->$key = $value;
                    break;
            }
        }
    }

    







    protected function parseUrl($url)
    {
        if (\PHP_VERSION_ID >= 50600 || false === strpos($url, '?')) {
            return parse_url($url);
        }

        $chunks = explode('?', $url);
        if (is_array($chunks)) {
            $result = parse_url($chunks[0]);
            if (is_array($result)) {
                $result['query'] = $chunks[1];
            }
            return $result;
        }

        return false;
    }
}
