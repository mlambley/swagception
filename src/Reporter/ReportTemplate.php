<?php
//Include the actual javascript so that it will work offline.
return <<<EOT
<!DOCTYPE html><html><head><title>{{title}}</title>{{javascript}}</head><body>{{body}}</body></html>
EOT;
