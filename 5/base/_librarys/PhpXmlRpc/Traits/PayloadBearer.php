<?php

namespace PhpXmlRpc\Traits;

trait PayloadBearer
{
    
    protected $payload;
    
    protected $content_type = 'text/xml';

    






    public function setPayload($payload, $contentType = '')
    {
        $this->payload = $payload;

        if ($contentType != '') {
            $this->content_type = $contentType;
        }

        return $this;
    }

    


    public function getPayload()
    {
        return $this->payload;
    }

    


    public function getContentType()
    {
        return $this->content_type;
    }
}
