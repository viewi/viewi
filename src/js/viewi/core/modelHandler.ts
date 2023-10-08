import { BaseComponent } from "./component/baseComponent"
import { InputType } from "./inputType"

export type ModelHandler = {
    getter: (instance: BaseComponent<any>) => any,
    setter: (instance: BaseComponent<any>, value: any) => void,
    inputType: InputType,
    isMultiple: boolean
}