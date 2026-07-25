<?php

// Boot the shared app instance before the suite starts so Leaf's error handler
// is registered outside of any test — keeps PHPUnit's handler-stack checks happy.
app();
