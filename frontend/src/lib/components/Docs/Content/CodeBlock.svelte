<script lang="ts">

    import './prism.css';

    export let code: string;
    export let lang: 
        'html' | 'js' 
        = 'html';

    // @ts-ignore
    import Prism from 'prismjs';

    function getCode() {

        let ret = code;

        // remove the first empty line
        ret = ret.replace(/^[^\S\r\n]*\n/, "");
        // remove the last empty line
        ret = ret.replace(/\n[^\S\r\n]*$/, "");

        let lines = ret.split("\n");
        let indent : null | number = null; // number of spaces to remove from each line

        lines = lines.map(line => {
            if (indent === null) {
                // find the indent
                const match = line.match(/^(\s*)/);
                indent = match ? match[1].length : 0;
            }

            if (line.substring(0, indent).trim() !== "") {
                return line;
            }

            // remove the indent
            line = line.substring(indent);

            return line;
        });

        ret = lines.join("\n");

        return Prism.highlight(ret, Prism.languages[lang], lang);

    }

</script>

<pre class="language-{lang}"><code>{@html getCode()}</code></pre>

