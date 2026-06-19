<?php


class TileAssistant extends IPSModule
{

    public function Create()
    {
         // Never delete this line!
         parent::Create();

         $this->RegisterPropertyInteger('message', 0);
         $this->SetVisualizationType(1);
    }

    public function ApplyChanges() {
        parent::ApplyChanges();
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
    }

    public function RequestAction($Ident, $Value)
    {
	    if($Ident === 'message' || $Ident === 'load') {
            $script = $this->ReadPropertyInteger($Ident);
            if($script && @IPS_GetScript($script)) {
                $res = IPS_RunScriptWaitEx($script, ['action'=>$Ident, 'data'=>json_decode($Value, true)]);
                $this->UpdateVisualizationValue($res);
            }
        }
    }

    public function GetVisualizationTile() {
        $module = file_get_contents(__DIR__ . '/module.html');
        return $module;
    }
}
