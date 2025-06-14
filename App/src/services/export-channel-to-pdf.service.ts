import { jsPDF } from 'jspdf';
import html2canvas from 'html2canvas';

const page = {
    maxHeightCanvas: 32767,
    html2canvasScale: 1.5,
    jpgCompression: 0.8,
    sizeReduction: 1.0,
    pageWidth: 148,
    pageHeight: 295
};

async function createChannelCanvas(chatbox: HTMLElement): Promise<HTMLCanvasElement> {
    const imgWidth = page.pageWidth * page.sizeReduction;

    const originalStyle = {
        height: chatbox.style.height,
        maxHeight: chatbox.style.maxHeight,
        overflow: chatbox.style.overflow
    };

    chatbox.style.height = 'auto';
    chatbox.style.maxHeight = 'none';
    chatbox.style.overflow = 'visible';

    try {
        const canvas = await html2canvas(chatbox, {
            scale: page.html2canvasScale,
            useCORS: true
        });
        return canvas;
    } catch (error) {
        console.error('Error creating canvas:', error);
        // Créer un canvas vide en cas d'erreur
        const canvas = document.createElement('canvas');
        return canvas;
    } finally {
        // Restaurer le style original de l'élément chatbox
        chatbox.style.height = originalStyle.height;
        chatbox.style.maxHeight = originalStyle.maxHeight;
        chatbox.style.overflow = originalStyle.overflow;
    }
}

export async function exportChannelToPDF(chatbox: HTMLElement): Promise<void> {
    const canvas = await createChannelCanvas(chatbox);
    const imgData = canvas.toDataURL('image/jpeg', page.jpgCompression);
    const pdf = new jsPDF({
        orientation: 'p',
        unit: 'mm',
        format: [page.pageWidth, page.pageHeight],
        compress: true
    });

    const imgWidth = page.pageWidth * page.sizeReduction;
    const imgHeight = canvas.height * imgWidth / canvas.width;
    let heightLeft = imgHeight;
    let position = 0;

    pdf.addImage(imgData, 'JPEG', (page.pageWidth-imgWidth)/2, position, imgWidth, imgHeight);
    heightLeft -= page.pageHeight;

    while (heightLeft >= 0) {
        position = heightLeft - imgHeight;
        pdf.addPage();
        pdf.addImage(imgData, 'JPEG', (page.pageWidth-imgWidth)/2, position, imgWidth, imgHeight);
        heightLeft -= page.pageHeight;
    }

    const now = new Date();
    const dateStr = now.toISOString().slice(0,10);
    const timeStr = now.toTimeString().slice(0,8).replace(/:/g, '-');
    pdf.save(`emush-comm-channel-${dateStr}_${timeStr}.pdf`);
}


export async function exportChannelToClipboard(chatbox: HTMLElement): Promise<void> {
    copyCanvasToClipboard(await createChannelCanvas(chatbox));
}

function copyCanvasToClipboard(canvas: HTMLCanvasElement) {
    canvas.toBlob(blob => {
        if (!blob) return;
        const item = new ClipboardItem({ 'image/png': blob });
        navigator.clipboard.write([item])
            .then(() => {
                console.log('Canvas copied to clipboard!');
            })
            .catch(err => {
                console.error('Error copying canvas: ', err);
            });
    });
}
