const dialogAddRow = `
    <form>
        <div class="form-group">
            <label>Идентификатор</label>
            <input type="text" id="id" class="id form-control"/>
        </div>
        <div class="form-group">
            <label>CSS-класс</label>
            <input type="text" id="class" class="form-control"/>
        </div>
    </form>
`;

const dialogAddColumn = `
    <form>
        <div class="form-group">
            <label>Идентификатор</label>
            <input type="text" name="id" class="id form-control"/>
        </div>
        <div class="form-group">
            <label>CSS-класс</label>
            <input type="text" name="class" class="form-control" list="column-classes"/>
            <datalist id="column-classes" >
                <option>col-md-12</option>
                <option>col-md-6</option>
                <option>col-md-4</option>
                <option>col-md-3</option>
                <option>col-md-2</option>
                <option>col-md-1</option>
            </datalist>                         
        </div>
        <div class="form-group">
            <label>Поле</label>
            <select name="field" ref="field" class="form-control"></select>
        </div>
        <div class="form-group">
            <label>Контент</label>
            <textarea name="content" class="form-control"></textarea>
        </div>
    </form>
`;

export {dialogAddRow, dialogAddColumn}