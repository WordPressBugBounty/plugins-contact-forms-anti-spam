<?php

declare(strict_types=1);

namespace Maspik\Admin\Rest;

use Maspik\Domain\Check\EmailBlacklistCheck;
use Maspik\Domain\Check\IpBlocklistCheck;
use Maspik\Domain\Check\PhoneCheck;
use Maspik\Domain\Check\TextBlacklistCheck;
use Maspik\Domain\Check\UrlBlacklistCheck;
use Maspik\Domain\Model\Field;
use Maspik\Domain\Model\FieldType;
use Maspik\Domain\Model\Submission;
use Maspik\Domain\Model\Violation;
use WP_REST_Request;
use WP_REST_Response;

/**
 * POST /maspik/v1/test-rule — "would this value be blocked?" for the rule
 * editors. Runs the *real* domain check against the rules the user is editing
 * (draft, unsaved), so the answer always matches what the engine would do — no
 * matching logic is reimplemented in the browser.
 *
 * Body: { type: 'text'|'email'|'url'|'ip'|'phone', rules: string[], value: string }
 */
final class RuleTesterController
{
    public function registerRoutes(): void
    {
        register_rest_route('maspik/v1', '/test-rule', [
            'methods' => 'POST',
            'callback' => [$this, 'test'],
            'permission_callback' => static function (): bool {
                return current_user_can('manage_options');
            },
        ]);
    }

    public function test(WP_REST_Request $request): WP_REST_Response
    {
        $params = (array) $request->get_json_params();
        $type = isset($params['type']) ? (string) $params['type'] : '';
        $value = isset($params['value']) ? (string) $params['value'] : '';
        $rules = [];
        if (isset($params['rules']) && is_array($params['rules'])) {
            foreach ($params['rules'] as $rule) {
                if (is_string($rule) && trim($rule) !== '') {
                    $rules[] = $rule;
                }
            }
        }

        if ($value === '') {
            return new WP_REST_Response(['blocked' => false, 'reason' => null]);
        }

        $violation = $this->run($type, $rules, $value);

        return new WP_REST_Response([
            'blocked' => $violation !== null,
            'reason' => $violation !== null ? $violation->reason : null,
        ]);
    }

    /** @param string[] $rules */
    private function run(string $type, array $rules, string $value): ?Violation
    {
        switch ($type) {
            case 'text':
                // The text blacklist protects both text and message fields; the
                // value is caught if either would block it.
                $check = new TextBlacklistCheck($rules);
                $violation = $check->check(new Field('test', FieldType::TEXT, $value));

                return $violation !== null
                    ? $violation
                    : $check->check(new Field('test', FieldType::TEXTAREA, $value));

            case 'email':
                return (new EmailBlacklistCheck($rules))->check(new Field('test', FieldType::EMAIL, $value));

            case 'url':
                return (new UrlBlacklistCheck($rules))->check(new Field('test', FieldType::URL, $value));

            case 'phone':
                return (new PhoneCheck($rules))->check(new Field('test', FieldType::TEL, $value));

            case 'ip':
                return (new IpBlocklistCheck($rules))->check(new Submission([], 'test', 'Test', $value));

            default:
                return null;
        }
    }
}
