<?php
namespace Codeception;


final class Events
{
    
    private function __construct()
    {
    }

    
    const MODULE_INIT = 'module.init';

    
    const SUITE_INIT = 'suite.init';

    
    const SUITE_BEFORE = 'suite.before';

    
    const SUITE_AFTER = 'suite.after';

    
    const TEST_START = 'test.start';

    
    const TEST_BEFORE = 'test.before';

    
    const STEP_BEFORE = 'step.before';

    
    const STEP_AFTER = 'step.after';

    
    const TEST_FAIL = 'test.fail';

    
    const TEST_ERROR = 'test.error';

    
    const TEST_PARSED = 'test.parsed';

    
    const TEST_INCOMPLETE = 'test.incomplete';

    
    const TEST_SKIPPED = 'test.skipped';

    
    const TEST_SUCCESS = 'test.success';

    
    const TEST_AFTER = 'test.after';

    
    const TEST_END = 'test.end';

    
    const TEST_FAIL_PRINT = 'test.fail.print';

    
    const RESULT_PRINT_AFTER = 'result.print.after';
}
