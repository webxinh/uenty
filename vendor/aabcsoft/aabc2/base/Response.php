<?php


namespace aabc\base;


class Response extends Component
{
    
    public $exitStatus = 0;


    
    public function send()
    {
    }

    
    public function clearOutputBuffers()
    {
        // the following manual level counting is to deal with zlib.output_compression set to On
        for ($level = ob_get_level(); $level > 0; --$level) {
            if (!@ob_end_clean()) {
                ob_clean();
            }
        }
    }
}
