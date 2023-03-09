/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
import interact from"interactjs";import DocumentService from"@typo3/core/document-service.js";import DataHandler from"@typo3/backend/ajax-data-handler.js";import Icons from"@typo3/backend/icons.js";import RegularEvent from"@typo3/core/event/regular-event.js";class DragDrop{constructor(){DocumentService.ready().then((()=>{DragDrop.initialize()}))}static initialize(){const e=document.querySelector(".module");new RegularEvent("wheel",(t=>{e.scrollLeft+=t.deltaX,e.scrollTop+=t.deltaY})).delegateTo(document,".draggable-dragging"),interact(DragDrop.draggableContentIdentifier).draggable({allowFrom:DragDrop.draggableContentHandleIdentifier,onstart:DragDrop.onDragStart,onmove:DragDrop.onDragMove,onend:DragDrop.onDragEnd}).pointerEvents({allowFrom:DragDrop.draggableContentHandleIdentifier}).on("move",(function(e){const t=e.interaction,r=e.currentTarget;if(t.pointerIsDown&&!t.interacting()&&"false"!=r.getAttribute("clone")){const a=r.cloneNode(!0);a.setAttribute("data-dragdrop-clone","true"),r.parentNode.insertBefore(a,r.nextSibling),t.start({name:"drag"},e.interactable,r)}})),interact(DragDrop.dropZoneIdentifier).dropzone({accept:this.draggableContentIdentifier,ondrop:DragDrop.onDrop,checker:(e,t,r,a,o)=>{const n=o.getBoundingClientRect();return t.pageX>=n.left&&t.pageX<=n.left+n.width&&t.pageY>=n.top&&t.pageY<=n.top+n.height}}).on("dragenter",(e=>{e.target.classList.add(DragDrop.dropPossibleHoverClass)})).on("dragleave",(e=>{e.target.classList.remove(DragDrop.dropPossibleHoverClass)}))}static onDragStart(e){e.target.dataset.dragStartX=(e.client.x-e.rect.left).toString(),e.target.dataset.dragStartY=(e.client.y-e.rect.top).toString(),e.target.style.width=getComputedStyle(e.target).getPropertyValue("width"),e.target.classList.add("draggable-dragging");const t=document.createElement("div");t.classList.add("draggable-copy-message"),t.textContent=TYPO3.lang["dragdrop.copy.message"],e.target.append(t),e.target.closest(DragDrop.columnIdentifier).classList.remove("active"),e.target.querySelector(DragDrop.dropZoneIdentifier).hidden=!0,document.querySelectorAll(DragDrop.dropZoneIdentifier).forEach((e=>{const t=e.parentElement.querySelector(DragDrop.addContentIdentifier);null!==t&&(t.hidden=!0,e.classList.add(DragDrop.validDropZoneClass))}))}static onDragMove(e){const t=document.querySelector(".module");e.target.style.left=e.client.x-parseInt(e.target.dataset.dragStartX,10)+"px",e.target.style.top=e.client.y-parseInt(e.target.dataset.dragStartY,10)+"px",e.delta.x<0&&e.pageX-20<0?t.scrollLeft-=20:e.delta.x>0&&e.pageX+20>t.offsetWidth&&(t.scrollLeft+=20),e.delta.y<0&&e.pageY-20-document.querySelector(".t3js-module-docheader").clientHeight<0?t.scrollTop-=20:e.delta.y>0&&e.pageY+20>t.offsetHeight&&(t.scrollTop+=20)}static onDragEnd(e){e.target.dataset.dragStartX="",e.target.dataset.dragStartY="",e.target.classList.remove("draggable-dragging"),e.target.style.width="unset",e.target.style.left="unset",e.target.style.top="unset",e.target.closest(DragDrop.columnIdentifier).classList.add("active"),e.target.querySelector(DragDrop.dropZoneIdentifier).hidden=!1,e.target.querySelector(".draggable-copy-message").remove(),document.querySelectorAll(DragDrop.dropZoneIdentifier+"."+DragDrop.validDropZoneClass).forEach((e=>{const t=e.parentElement.querySelector(DragDrop.addContentIdentifier);null!==t&&(t.hidden=!1),e.classList.remove(DragDrop.validDropZoneClass)})),document.querySelectorAll(DragDrop.draggableContentCloneIdentifier).forEach((e=>{e.remove()}))}static onDrop(e){const t=e.target,r=e.relatedTarget,a=DragDrop.getColumnPositionForElement(t),o=parseInt(r.dataset.uid,10);if("number"==typeof o&&o>0){const n={},s=t.closest(DragDrop.contentIdentifier).dataset.uid;let l;l=void 0===s?parseInt(t.closest("[data-page]").dataset.page,10):0-parseInt(s,10);let d=parseInt(r.dataset.languageUid,10);-1!==d&&(d=parseInt(t.closest("[data-language-uid]").dataset.languageUid,10));let g=0;0!==l&&(g=a);const i=e.dragEvent.ctrlKey||t.classList.contains("t3js-paste-copy"),c=i?"copy":"move";n.cmd={tt_content:{[o]:{[c]:{action:"paste",target:l,update:{colPos:g,sys_language_uid:d}}}}},DragDrop.ajaxAction(t,r,n,i).then((()=>{const e=document.querySelector(`.t3-page-column-lang-name[data-language-uid="${d}"]`);if(null===e)return;const t=e.dataset.flagIdentifier,a=e.dataset.languageTitle;Icons.getIcon(t,Icons.sizes.small).then((e=>{const t=r.querySelector(".t3js-flag");t.title=a,t.innerHTML=e}))}))}}static ajaxAction(e,t,r,a){const o=Object.keys(r.cmd).shift(),n=parseInt(Object.keys(r.cmd[o]).shift(),10),s={component:"dragdrop",action:a?"copy":"move",table:o,uid:n};return DataHandler.process(r,s).then((r=>{if(r.hasErrors)throw r.messages;e.parentElement.classList.contains(DragDrop.contentIdentifier.substring(1))?e.closest(DragDrop.contentIdentifier).after(t):e.closest(DragDrop.dropZoneIdentifier).after(t),a&&self.location.reload()}))}static getColumnPositionForElement(e){const t=e.closest("[data-colpos]");return null!==t&&void 0!==t.dataset.colpos&&parseInt(t.dataset.colpos,10)}}DragDrop.contentIdentifier=".t3js-page-ce",DragDrop.draggableContentIdentifier=".t3js-page-ce-sortable",DragDrop.draggableContentHandleIdentifier=".t3js-page-ce-draghandle",DragDrop.draggableContentCloneIdentifier="[data-dragdrop-clone]",DragDrop.dropZoneIdentifier=".t3js-page-ce-dropzone-available",DragDrop.columnIdentifier=".t3js-page-column",DragDrop.validDropZoneClass="active",DragDrop.dropPossibleHoverClass="t3-page-ce-dropzone-possible",DragDrop.addContentIdentifier=".t3js-page-new-ce";export default new DragDrop;