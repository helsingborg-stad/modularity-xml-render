const { translation, posttypes } = modXMLRender;

function dropAreas(view) {
    let dropAreas = [];
    switch (view) {
        case 'export':
            dropAreas.push({
                id: 'content',
                limit: null,
                label: translation.content,
                postTypes: posttypes,
            });
            break;
        case 'list':
            dropAreas.push({ id: 'heading', limit: 1, label: translation.heading });
            break;
        case 'accordion':
            dropAreas.push(
                { id: 'heading', limit: 1, label: translation.heading },
                { id: 'content', limit: null, label: translation.content }
            );
            break;
        case 'accordiontable':
            dropAreas.push(
                { id: 'heading', limit: null, label: translation.headings },
                { id: 'content', limit: null, label: translation.content }
            );
            break;
        case 'table':
            dropAreas.push({ id: 'heading', limit: null, label: translation.headings });
            break;
    }

    return dropAreas;
}

export { dropAreas, translation, posttypes };
