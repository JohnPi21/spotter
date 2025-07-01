function get(haystack: any, path: string, defaultValue?: any){

    let levels = path.includes('.') === true ? path.split('.') : [path];

    let obj = haystack;

    for(let level of levels){

        if(obj == null || ! (level in obj)){
            return defaultValue ?? null; 
        }

        obj = obj[level];
    }

    return obj;
}


export const useArray = { get };