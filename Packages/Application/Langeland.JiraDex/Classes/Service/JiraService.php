<?php

namespace Langeland\JiraDex\Service;

use chobie\Jira\Api;
use Neos\Cache\Frontend\VariableFrontend;
use Neos\Flow\Annotations as Flow;

/**
 * Class SprintService
 * @package Langeland\JiraDex\Service
 *
 * @see https://docs.atlassian.com/jira/REST/1000.837.0/
 * @see https://docs.atlassian.com/jira-software/REST/cloud/
 *
 * @Flow\Scope("singleton")
 */
class JiraService
{

    /**
     * @var Api
     * @Flow\Inject
     */
    protected $jira;

    /**
     * @var VariableFrontend
     * @Flow\Inject
     */
    protected $apiCache;

    /**
     * @return array
     */
    public function getBoards()
    {
        $isLast = false;
        $params = array('startAt' => 0, 'maxResults' => 50);
        $boards = array();
        while ($isLast == false) {
            $response = $this->jira->api(Api::REQUEST_GET, sprintf('/rest/agile/1.0/board'), $params);
            $boards = array_merge($boards, $response->getResult()['values']);
            if ($response->getResult()['isLast']) {
                break;
            }
            $params['startAt'] = $params['startAt'] + $params['maxResults'];
        }

        return $boards;
    }

    /**
     * Returns all sprints from a board, for a given board Id.
     * This only includes sprints that the user has permission to view.
     *
     * @see https://docs.atlassian.com/jira-software/REST/cloud/#agile/1.0/board/{boardId}/sprint
     * @param integer $boardId
     * @param array $state Filters results to sprints in specified states. Valid values: future, active, closed. You can define multiple states separated by commas, e.g. state=active,closed
     * @return array
     */
    public function getSprints($boardId, $state = array())
    {
        $isLast = false;
        $params = array('startAt' => 0, 'maxResults' => 50);
        $sprints = array();
        while ($isLast == false) {
            $response = $this->jira->api(Api::REQUEST_GET, sprintf('/rest/agile/1.0/board/%s/sprint', $boardId), $params);
            $sprints = array_merge($sprints, $response->getResult()['values']);
            if ($response->getResult()['isLast']) {
                break;
            }
            $params['startAt'] = $params['startAt'] + $params['maxResults'];
        }

        $sprints = array_filter($sprints, function ($v) {
            return $v['state'] == 'active';
        });

        return $sprints;
    }

    /**
     * Returns the sprint for a given sprint Id.
     * The sprint will only be returned if the user can view the board that the sprint was created on, or view at least one of the issues in the sprint.
     *
     * @see https://docs.atlassian.com/jira-software/REST/cloud/#agile/1.0/sprint-getSprint
     * @param integer $sprintId
     * @return array
     */
    public function getSprint($sprintId)
    {
        $response = $this->jira->api(Api::REQUEST_GET, sprintf('/rest/agile/1.0/sprint/%s', $sprintId));

        return $response->getResult();
    }

    /**
     * Returns all issues in a sprint, for a given sprint Id.
     * This only includes issues that the user has permission to view. By default, the returned issues are ordered by rank.
     *
     * @see https://docs.atlassian.com/jira-software/REST/cloud/#agile/1.0/sprint-getIssuesForSprint
     * @param integer $sprintId Filters results using a JQL query. If you define an order in your JQL query, it will override the default order of the returned issues.
     * @param string|null $jql
     * @param string|null $fields The list of fields to return for each issue. By default, all navigable and Agile fields are returned.
     * @param string|null $expand A comma-separated list of the parameters to expand.
     * @return array
     */
    public function getIssuesForSprint($sprintId, $jql = null, $fields = null, $expand = null)
    {
        $callIdentifier = sha1(json_encode(func_get_args()));

        if (!$this->apiCache->has($callIdentifier)) {

            $isLast = false;
            $params = array('startAt' => 0, 'maxResults' => 50);

            if (!is_null($jql)) {
                $params['jql'] = $jql;
            }

            $issues = array();
            while ($isLast == false) {
                $response = $this->jira->api(Api::REQUEST_GET, sprintf('/rest/agile/1.0/sprint/%s/issue', $sprintId), $params);
                $issues = array_merge($issues, $response->getIssues());

                if ($response->getTotal() <= count($issues)) {
                    break;
                }
                $params['startAt'] = $params['startAt'] + $params['maxResults'];
            }

            $this->apiCache->set($callIdentifier, $issues);
        } else {
            $issues = $this->apiCache->get($callIdentifier);
        }

        return $issues;
    }


}