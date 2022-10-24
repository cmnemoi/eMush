export default class WhiteOutlinePipeline extends Phaser.Renderer.WebGL.Pipelines.PostFXPipeline
{
    /**
     * @param {Phaser.Game} game - the controller of the game instance
     */
    constructor(game: Phaser.Game)
    {
        super({
            name: 'WhiteOutline',
            game: game,
            fragShader: `
                precision mediump float;
                // Scene buffer
                uniform sampler2D uMainSampler; 
                varying vec2 outTexCoord;
                
                // Effect parameters
                uniform vec2 texSize;
                uniform float thickness;
                uniform vec3 outlineColor;
                const float DOUBLE_PI = 3.14159265358979323846264 * 2.;
                void main() {
                  vec4 front = texture2D(uMainSampler, outTexCoord);
                  if (thickness > 0.0) {
                    vec2 mag = vec2(thickness/texSize.x, thickness/texSize.y);
                    vec4 curColor;
                    float maxAlpha = front.a;
                    vec2 offset;
                    for (float angle = 0.; angle < DOUBLE_PI; angle += 0.1) {
                        offset = vec2(mag.x * cos(angle), mag.y * sin(angle));        
                        curColor = texture2D(uMainSampler, outTexCoord + offset);
                        maxAlpha = max(maxAlpha, curColor.a);
                    }
                    vec3 resultColor = front.rgb + (outlineColor.rgb * (1. - front.a)) * maxAlpha;
                    gl_FragColor = vec4(resultColor, maxAlpha);
                  } else {
                    gl_FragColor = front;
                  }
                }
        `
        });
    }
}
