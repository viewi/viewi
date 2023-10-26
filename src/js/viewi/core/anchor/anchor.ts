import { HtmlNodeType } from "../node/htmlNodeType"

export type Anchor = {
    target: HtmlNodeType,
    current: number,
    added: number,
    invalid: number[]
}