function hydrateDOM() {
    var trimWhitespaceRegex = /^\s+|\s+$/;
    /**
     * 
     * @param {vNode} node 
     * @param {Node} domElement 
     */
    var hydrateDOM = function (node, domElement) {
        // nodeName - tag name
        // nodeType - 1 tag, 3 text, 8 comment
        // nodeValue for text and comment
        var same = node.domNode.nodeType === domElement.nodeType;
        if (same) {
            if (node.domNode.nodeType === 3 || node.domNode.nodeType == 8) {
                same = node.domNode.nodeValue.replace(trimWhitespaceRegex, '') == domElement.nodeValue.replace(trimWhitespaceRegex, '');
                if (same) {
                    node.domNode = domElement;
                }
            } else if (node.domNode.nodeType === 1) {
                // compare 1. tag name, 2.attributes, 3. children, 4. attach events
                // 1. tag name
                same = node.domNode.nodeName == domElement.nodeName;
                if (same) {
                    var oldParent = node.domNode;
                    node.domNode = domElement;
                    var s = 0; // shift for virtual nodes
                    var shiftDOM = 0; // shift for DOM children
                    var count = domElement.childNodes.length;
                    var maxNodes = node.children ? node.children.length : 0;
                    //if (count < maxNodes) {
                    if (node.children) {
                        // normalize
                        if (
                            node.children[maxNodes - 1].domNode
                            && node.children[maxNodes - 1].domNode.nodeType === 3
                            && (domElement.childNodes.length == 0 || domElement.childNodes[count - 1].nodeType !== 3)
                            && /^\s*$/.test(node.children[maxNodes - 1].domNode.nodeValue)
                        ) {
                            // oldParent.removeChild(node.children[maxNodes - 1].domNode);
                            // node.children.splice(maxNodes - 1, 1);
                            // node.children[maxNodes - 2].nextNode = null;
                            domElement.appendChild(node.children[maxNodes - 1].domNode);
                        }

                        if (
                            node.children[0].domNode
                            && node.children[0].domNode.nodeType === 3
                            && domElement.childNodes[0].nodeType !== 3
                            && /^\s*$/.test(node.children[0].domNode.nodeValue)
                        ) {
                            // oldParent.removeChild(node.children[0].domNode);
                            // node.children.splice(0, 1);
                            // node.children[0].previousNode = null;
                            domElement.insertBefore(node.children[0].domNode, domElement.childNodes[0]);
                        }
                        maxNodes = node.children.length;
                        count = domElement.childNodes.length;
                    }
                    var nodesToRemove = [];
                    var vNodes = [];
                    for (var i = 0; i < maxNodes; i++) {
                        if (node.children[i].rawNodes) {
                            vNodes = vNodes.concat(node.children[i].rawNodes);
                        } else if (node.children[i].isVirtual) {
                            collectVirtual(node.children[i], vNodes);
                        } else {
                            vNodes.push(node.children[i]);
                        }
                    }
                    var vCount = vNodes.length;
                    for (var i = 0; i + shiftDOM < count; i++) {
                        while (i + s < vCount && !vNodes[i + s].domNode) { // TODO: support virtual nodes, dig deeper
                            s++;
                        }
                        if (i + s < vCount) {
                            if (vNodes[i + s].type) {
                                var sameChild = hydrateDOM(vNodes[i + s], domElement.childNodes[i + shiftDOM]);
                                if (!sameChild) {
                                    // try to find next match
                                    var foundNext = false;
                                    for (var ni = i + shiftDOM + 1; ni < count; ni++) {
                                        foundNext = hydrateDOM(vNodes[i + s], domElement.childNodes[ni]);
                                        if (foundNext) {
                                            // remove all not matched before
                                            for (var ri = i + shiftDOM; ri < ni; ri++) {
                                                nodesToRemove.push(ri);
                                            }
                                            shiftDOM += ni - (i + shiftDOM);
                                            break;
                                        }
                                    }
                                    if (foundNext) {
                                        continue;
                                    }
                                    // reattach parent
                                    if (node.domNode !== vNodes[i + s].domNode.parentNode) {
                                        if (node.domNode.childNodes.length > i + shiftDOM) {
                                            node.domNode.replaceChild(vNodes[i + s].domNode, node.domNode.childNodes[i + shiftDOM]);
                                        } else {
                                            node.domNode.appendChild(vNodes[i + s].domNode);
                                        }
                                        hydrateDOM(vNodes[i + s], domElement.childNodes[i + shiftDOM]);
                                    } else {
                                        // two copies, remove one
                                        nodesToRemove.push(i);
                                    }
                                }
                            } else {
                                vNodes[i + s].domNode = domElement.childNodes[i + shiftDOM]; // TODO: compare attributes
                            }
                        } else {
                            // no more nodes to compare, remove dom element 
                            nodesToRemove.push(i + shiftDOM);
                        }
                    }
                    // append the rest of it
                    for (var i = count; i + s < vCount; i++) {
                        // can be null
                        if (vNodes[i + s].domNode) { // TODO: merge text nodes
                            node.domNode.appendChild(vNodes[i + s].domNode);
                        }
                    }
                    if (nodesToRemove.length > 0) {
                        // var oldTotal = node.children ? node.children.length : 0;
                        for (var k = nodesToRemove.length - 1; k >= 0; k--) {
                            var nodeIndex = nodesToRemove[k];
                            // if (nodeIndex < oldTotal) {
                            //     // replace
                            //     domElement.replaceChild(node.children[nodeIndex].domNode, domElement.childNodes[nodeIndex]);
                            // } else {
                            if (['BODY', 'HEAD'].includes(domElement.childNodes[nodeIndex].nodeName)) {
                                // unexpected output from the server, replace rendered
                                domElement.replaceChild(node.children.first(
                                    function (x) { return x.domNode.nodeName && x.domNode.nodeName === domElement.childNodes[nodeIndex].nodeName; }
                                ).domNode, domElement.childNodes[nodeIndex]);
                            } else {
                                domElement.removeChild(domElement.childNodes[nodeIndex]);
                            }
                            // }
                        }
                    }
                    count = domElement.childNodes.length;
                    if (count > vCount) {
                        for (var k = count - 1; k >= vCount; k--) {
                            domElement.removeChild(domElement.childNodes[k]);
                        }
                    }
                    if (vCount > count) {
                        for (var k = count; k < vCount; k++) {
                            if (vNodes[k].domNode && !vNodes[k].type) { // raw html from external js
                                domElement.appendChild(vNodes[k].domNode);
                            }
                        }
                    }
                    // 4. attach events
                    if (node.attributes) {
                        for (var a in node.attributes) {
                            renderAttribute(node.domNode, node.attributes[a], true);
                        }
                    }
                }
            }
        }
        return same;
    }
    return hydrateDOM;
}