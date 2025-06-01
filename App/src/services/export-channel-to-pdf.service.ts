import { jsPDF } from 'jspdf';
import html2canvas from 'html2canvas';

export async function exportChannelToPDF(chatbox: HTMLElement, toPDF: boolean): Promise<void> {
    const maxHeightCanvas = 32767;
    const html2canvasScale = 1.5;
    const jpgCompression = 0.8;
    const sizeReduction = 1.0;
    const pageWidth = 148;
    const pageHeight = 295;
    const imgWidth = pageWidth * sizeReduction;

    const originalStyle = {
        height: chatbox.style.height,
        maxHeight: chatbox.style.maxHeight,
        overflow: chatbox.style.overflow
    };

    chatbox.style.height = 'auto';
    chatbox.style.maxHeight = 'none';
    chatbox.style.overflow = 'visible';

    html2canvas(chatbox, {
        scale: html2canvasScale,
        useCORS: true
    }).then(canvas => {
        const imgData = canvas.toDataURL('image/jpeg', jpgCompression);
        if (!toPDF) {
            copyCanvasToClipboard(canvas);
        } else {
            const pdf = new jsPDF({
                orientation: 'p',
                unit: 'mm',
                format: [pageWidth, pageHeight],
                compress: true
            });

            const imgHeight = canvas.height * imgWidth / canvas.width;
            let heightLeft = imgHeight;
            let position = 0;

            pdf.addImage(imgData, 'JPEG', (pageWidth-imgWidth)/2, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;

            while (heightLeft >= 0) {
                position = heightLeft - imgHeight;
                pdf.addPage();
                pdf.addImage(imgData, 'JPEG', (pageWidth-imgWidth)/2, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
            }
            chatbox.style.height = originalStyle.height;
            chatbox.style.maxHeight = originalStyle.maxHeight;
            chatbox.style.overflow = originalStyle.overflow;
            const now = new Date();
            const dateStr = now.toISOString().slice(0,10);
            const timeStr = now.toTimeString().slice(0,8).replace(/:/g, '-');
            pdf.save(`emush-comm-channel-${dateStr}_${timeStr}.pdf`);
        }
    });
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
