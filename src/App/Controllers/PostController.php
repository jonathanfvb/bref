<?php

namespace Agendanet\App\Controllers;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Agendanet\App\Http\Response\JsonResponse;
use Agendanet\Domain\UseCase\CreateSchedule;
use Agendanet\App\Http\Exceptions\BadRequestException;
use Agendanet\App\Http\Exceptions\BaseException;
use Agendanet\Domain\DTO\CreateScheduleRequest;

class PostController
{
    private $createSchedule;
    
    public function __construct(CreateSchedule $createSchedule)
    {
        $this->createSchedule = $createSchedule;
    }
    
    public function handler(Request $request, Response $response)
    {
        try {            
            $createScheduleRequest = $this->mapHttpRequestToUseCaseRequest(
                $request
            );
            $payload = $this->createSchedule->execute($createScheduleRequest);
            $response->getBody()->write(json_encode($payload));
        } catch (BaseException $e) {
            $response->getBody()->write(json_encode($e->toArray()));
        } catch (Exception $e) {
            $response->getBody()->write(json_encode([
                'code' => 500,
                'message' => $e->getMessage()
            ]));
        } finally {
            return JsonResponse::send($response);
        }        
    }
    
    private function mapHttpRequestToUseCaseRequest(Request $request)
    {
        $params = json_decode($request->getBody()->getContents(), true);
        if (empty($params['user_phone'])) {
            throw new BadRequestException('user_phone is a mandatory parameter');
        }
        if (empty($params['user_name'])) {
            throw new BadRequestException('user_name is a mandatory parameter');
        }
        if (empty($params['doctor_id'])) {
            throw new BadRequestException('doctor_id is a mandatory parameter');
        }
        if (empty($params['schedule_datetime'])) {
            throw new BadRequestException('schedule_datetime is a mandatory parameter');
        }
        
        return new CreateScheduleRequest(
            $params['user_phone'], 
            $params['user_name'], 
            $params['doctor_id'], 
            $params['schedule_datetime']
        );
    }
}
