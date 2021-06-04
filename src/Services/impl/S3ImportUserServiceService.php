<?php


namespace App\Services\impl;


use App\Constants\ImportActionConstant;
use App\Constants\ImportStatusConstant;
use App\Helpers\RabbitMQHelper;
use App\Models\Dossier;
use App\Services\ImportUserService;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use YaangVu\Constant\CodeConstant;
use YaangVu\LaravelBase\Services\impl\BaseService;

class S3ImportUserServiceService implements ImportUserService
{
    use RabbitMQHelper;

    /**
     * @param Request $request
     *
     * @return Model|Dossier
     * @throws Exception
     */
    function importStaffs(Request $request): Model|Dossier
    {
        $request->merge(['action' => ImportActionConstant::STAFF]);

        return $this->import($request);
    }

    /**
     * @param Request $request
     *
     * @return Model|Dossier
     * @throws Exception
     */
    function importStudents(Request $request): Model|Dossier
    {
        $request->merge(['action' => ImportActionConstant::STUDENT]);

        return $this->import($request);
    }

    /**
     * @param Request $request
     *
     * @return Model|Dossier
     * @throws Exception
     */
    function import(Request $request): Model|Dossier
    {
        BaseService::doValidate($request, ['file_url' => 'required']);
        $scId = $request->header(CodeConstant::SC_ID);
        $request->merge(
            [
                'status'            => ImportStatusConstant::PENDING,
                CodeConstant::SC_ID => $scId,
                'storage_type'      => 'S3'
            ]
        );

        $dossier = (new DossierService())->add($request);

        $this->_pushToExchange($dossier);

        return $dossier;
    }

    /**
     * Push to RabbitMQ after import
     *
     * @param Model|Dossier $model
     *
     * @throws Exception
     */
    function _pushToExchange(Model|Dossier $model): void
    {
        $rabbitMqData = [
            '_id'      => $model->_id,
            'file_url' => $model->file_url,
            'action'   => $model->action
        ];
        $this->pushToExchange($rabbitMqData, 'IMPORT_USERS', AMQPExchangeType::DIRECT, 'keycloak');
    }
}
