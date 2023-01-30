<script setup>
import Quill from 'quill';
import Parchment from "parchment";
import emitter from 'tiny-emitter/instance';

import { PlusIcon, PhotoIcon, CodeBracketIcon, EllipsisHorizontalCircleIcon } from "@heroicons/vue/24/outline";
import { onMounted, ref } from "vue";

// Blot
import DividerBlot from './DividerBlot.js';
import HTMLBlot from './HTMLBlot.js';
import ImageBlot from './ImageBlot.js';
import QuillClipboard from './QuillClipboard.js';
import QuillLink from './QuillLink.js';

// Component
import HTMLEmbedder from './HTMLEmbedder.vue';
import ImageUploader from './ImageUploader.vue';

import icon from '~node/quill/assets/icons/header-3.svg?raw';

let editor = ref(null);

const props = defineProps({
    value: {
        type: String,
        default: "",
    },
    postId: {
        type: String,
    },
});

const emit = defineEmits([
    "input",
    'adding',
    "openingHTMLEmbedder",
    "openingImageUploader",
]);

onMounted(async () => {
    await Promise.all([createEditor(), handleEditorValue()]);

    handleClicksInsideEditor();

    initSideControls();
});

/**
* Create an instance of the editor.
*/
const createEditor = () => {
    let icons;

    // register embed here
    Quill.register(DividerBlot, true);
    Quill.register(HTMLBlot, true);
    Quill.register(ImageBlot, true);
    Quill.register(QuillLink, true);
    Quill.register("modules/clipboard", QuillClipboard, true);

    icons = Quill.import('ui/icons');
    icons.header[3] = icon;

    let quill = new Quill(editor.value, {
        modules: {
            syntax: true,
            toolbar: [
                ["bold", "italic", "underline", "strike", "code"],
                [{ header: "2" }, { header: "3" }],
                [{ list: "ordered" }, { list: "bullet" }, "link"],
                ["blockquote", "code-block"],
            ],
        },
        theme: "bubble",
        scrollingContainer: "html, body",
        placeholder: "Tell Your Story",
    });

    return (editor = quill);
}

/**
 * Handle the editor value.
 */
const handleEditorValue = () => {
    editor.root.innerHTML = props.value;

    editor.on("text-change", () => {
        emit("input", editor.getText() ? editor.root.innerHTML : "");
    });
};

/**
 * Handle click events inside the editor.
 */
const handleClicksInsideEditor = () => {
    editor.root.addEventListener("click", (ev) => {
        let blot = Parchment.find(ev.target, true);

        if (blot instanceof ImageBlot) {
            var values = blot.value(blot.domNode)["captioned-image"];

            values.existingBlot = blot;

            openImageUploader(values);
        }

        if (blot instanceof HTMLBlot) {
            var values = blot.value(blot.domNode)["html"];

            values.existingBlot = blot;

            openingHTMLEmbedder(values);
        }
    });
};

/**
 * Init the side controls.
 */
const initSideControls = () => {
    let Block = Quill.import("blots/block");

    editor.on(Quill.events.EDITOR_CHANGE, (eventType, range) => {
        let sidebarControls = document.getElementById("sidebar-controls");

        if (eventType !== Quill.events.SELECTION_CHANGE) return;

        if (range == null) return;

        if (range.length === 0) {
            let [block, offset] = editor.scroll.descendant(Block, range.index);

            if (
                block != null &&
                block.domNode.firstChild instanceof HTMLBRElement
            ) {
                let lineBounds = editor.getBounds(range);

                sidebarControls.classList.remove("active");

                sidebarControls.style.display = "block";

                sidebarControls.style.left = lineBounds.left - 50 + "px";
                sidebarControls.style.top = lineBounds.top - 2 + "px";
            } else {
                sidebarControls.style.display = "none";

                sidebarControls.classList.remove("active");
            }
        } else {
            sidebarControls.style.display = "none";

            sidebarControls.classList.remove("active");
        }
    });
};

/**
 * Show the side controls.
 */
const showSideControls = () => {
    document.getElementById("sidebar-controls").classList.toggle("active");

    editor.focus();
};

/**
 * Add a new captioned image to the content.
 */
const applyImage = ({ url, caption, existingBlot, layout }) => {
    let values = {
        url: url,
        caption: caption,
        layout: layout,
    };

    if (existingBlot) {
        return existingBlot.replaceWith("captioned-image", values);
    }

    let range = editor.getSelection(true);

    editor.insertEmbed(
        range.index,
        "captioned-image",
        values,
        Quill.sources.USER
    );

    editor.setSelection(range.index + 1, Quill.sources.SILENT);
};

/**
 * Add a divider to the content.
 */
const addDivider = () => {
    let range = editor.getSelection(true);

    editor.insertText(range.index, "\n", Quill.sources.USER);
    editor.insertEmbed(range.index + 1, "divider", true, Quill.sources.USER);
    editor.setSelection(range.index + 2, Quill.sources.SILENT);
};

/**
 * Add a new HTML blot to the content.
 */
const applyHTML = ({ content, existingBlot }) => {
    let values = {
        content: content,
    };

    let range = editor.getSelection(true);

    if (existingBlot) {
        return existingBlot.replaceWith("html", values);
    }

    editor.insertEmbed(range.index, "html", values, Quill.sources.USER);

    editor.setSelection(range.index + 1, Quill.sources.SILENT);
};

const openingHTMLEmbedder = (data = null) => {
    emitter.emit("openingHTMLEmbedder", data);
};

const openImageUploader = (data = null) => {
    emitter.emit("openingImageUploader", data);
};

</script>

<template>
    <div class="relative m-5">

        <div id="sidebar-controls">
            <button id="show-controls"
                class="inline-flex items-center justify-center rounded-full w-8 h-8 border border-slate-400 text-slate-400 hover:bg-indigo-400 hover:border-slate-400 hover:text-white"
                @click="showSideControls">
                <PlusIcon class="w-5 h-5" />
            </button>
            <div class="controls hidden pl-4 bg-contrast">
                <button @click="openImageUploader()">
                    <PhotoIcon class="w-5 h-5" />
                </button>
                <button @click="openingHTMLEmbedder()">
                    <CodeBracketIcon class="w-5 h-5" />
                </button>
                <button @click="addDivider">
                    <EllipsisHorizontalCircleIcon class="w-5 h-5" />
                </button>
            </div>
        </div>

        <div ref="editor" />

        <!-- <ImageUploader post-id="postId" @updated="applyImage" /> -->
        <HTMLEmbedder post-id="postId" @adding="applyHTML" />

    </div>
</template>

<style lang="css">
.ql-editor {
    @apply font-sans text-lg leading-loose text-slate-800;
    padding: 0;
    word-break: break-word;
    overflow-y: visible;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -ms-flex-direction: column;
    flex-direction: column;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
}

.ql-editor p,
.ql-editor ul,
.ql-editor ol,
.ql-editor h1,
.ql-editor h2,
.ql-editor h3,
.ql-editor blockquote,
.ql-editor pre {
    min-width: 100%;
}

.ql-editor h2 {
    margin-top: 0 !important;
    margin-bottom: 33px !important;
    font-size: 1.5rem;
    font-weight: bold;
    line-height: 2.6rem;
}

.ql-editor h3 {
    margin-bottom: 33px !important;
    font-size: 17px !important;
    font-weight: bold;
    line-height: 2.6rem;
}

.ql-editor p,
.ql-editor ol,
.ql-editor ul,
.ql-editor pre,
.ql-editor blockquote {
    margin-bottom: 2px !important;
}

.ql-bubble .ql-editor pre.ql-syntax {
    background: rgba(238, 238, 238, 0.35);
    border: solid 2px rgba(0, 0, 0, 0.05);
    color: #000;
    overflow-x: auto;
    padding: 0.5em;
}

.ql-editor h1,
.ql-editor h2 {
    margin-top: 56px;
    margin-bottom: 15px;
}

.ql-editor ol,
.ql-editor ul {
    padding-left: 0;
}

.ql-editor ol li,
.ql-editor ul li {
    margin-bottom: 20px;
}

.ql-bubble .ql-editor a {
    @apply text-indigo-800 no-underline cursor-pointer;
}

.ql-container hr {
    border: none;
    @apply text-slate-600 my-5;
    letter-spacing: 1em;
    text-align: center;
    width: 72px;
    height: 36px;
}

.ql-container hr:before {
    content: "...";
}

#sidebar-controls {
    display: none;
    position: absolute;
    @apply z-10;
}

#sidebar-controls .controls button {
    @apply inline-flex bg-white text-slate-400 rounded-full w-8 h-8 border border-slate-400 text-center items-center justify-center mx-0.5;
}

#sidebar-controls .controls button:hover {
    @apply border-indigo-400 bg-indigo-400 text-white;
}

#sidebar-controls button:focus {
    @apply outline-none;
}

#sidebar-controls.active .controls {
    @apply inline-block;
}

.inline_html {
    position: relative;
    margin-bottom: 33px !important;
}

.inline_html:hover {
    box-shadow: 0 0 0 3px #4040c8;
}

.inline_html:after {
    content: "";
    display: block;
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    z-index: 10;
    cursor: default;
}

.embedded_image {
    margin-bottom: 33px !important;
    cursor: default;
}

.embedded_image[data-layout="wide"] img {
    max-width: 1024px;
}

.embedded_image img {
    max-width: 100%;
    height: auto;
    margin: 0 auto;
    display: block;
}

.embedded_image:hover img {
    box-shadow: 0 0 0 3px #4040c8;
}

.embedded_image p {
    text-align: center;
    margin-bottom: 0 !important;
}

.ql-editor.ql-blank::before {
    color: #9ba3aa;
    left: 0;
    font-style: normal;
}

input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus textarea:-webkit-autofill,
textarea:-webkit-autofill:hover textarea:-webkit-autofill:focus,
select:-webkit-autofill,
select:-webkit-autofill:hover,
select:-webkit-autofill:focus {
    border: none;
    -webkit-text-fill-color: #22292f;
    -webkit-box-shadow: 0 0 0px 1000px #ffffff inset;
    transition: background-color 5000s ease-in-out 0s;
    color: #22292f;
}
</style>