<?php
use RedBeanPHP\Facade as RedBean;
use Respect\Validation\Validator as DataValidator;

/**
 * @api {post} /staff/get-tickets Get tickets
 * @apiVersion 4.11.0
 *
 * @apiName Get tickets
 *
 * @apiGroup Staff
 *
 * @apiDescription This path retrieves all tickets in the staff member's departments.
 *
 * @apiPermission staff1
 *
 * @apiParam {Number} page The page number.
 * @apiParam {bool} closed Include closed tickets in the response.
 * @apiParam {Number} departmentId The id of the department searched
 *
 * @apiUse NO_PERMISSION
 * @apiUse INVALID_PAGE
 *
 * @apiSuccess {Object} data Information about a tickets and quantity of pages.
 * @apiSuccess {[Ticket](#api-Data_Structures-ObjectTicket)[]} data.tickets Array of tickets in the staff's departments.
 * @apiSuccess {Number} data.page Number of current page.
 * @apiSuccess {Number} data.pages Quantity of pages.
 *
 */

class GetTicketStaffController extends Controller {
    const PATH = '/get-tickets';
    const METHOD = 'POST';

    public function validations() {
        return [
            'permission' => 'staff_1',
            'requestData' => [
                'page' => [
                    'validation' => DataValidator::numeric(),
                    'error' => ERRORS::INVALID_PAGE
                ]
            ]
        ];
    }

    public function handler() {
        $user = Controller::getLoggedUser();
        $closed = Controller::request('closed');
        $page = Controller::request('page');
        $departmentId = Controller::request('departmentId');
        $offset = ($page-1)*10;

        if (Ticket::isTableEmpty()) {
            Response::respondSuccess([
                'tickets' => [],
                'page' => $page,
                'pages' => 0
            ]);
            return;
        }

        // Build department scope from staff's assigned departments
        $query = ' (';
        foreach ($user->sharedDepartmentList as $department) {
            $query .= 'department_id=' . $department->id . ' OR ';
        }
        $query .= 'FALSE)';

        if (!$closed) {
            $query .= ' AND closed = 0';
        }

        if ($departmentId) {
            $query .= ' AND department_id=' . $departmentId;
        }

        $countTotal = Ticket::count($query);

        $query .= ' ORDER BY unread_staff DESC, ticket_number DESC';
        $query .= ' LIMIT 10 OFFSET ' . $offset;

        $ticketList = Ticket::find($query);

        Response::respondSuccess([
            'tickets' => $ticketList->toArray(true),
            'page' => $page,
            'pages' => ceil($countTotal / 10)
        ]);
    }
}
