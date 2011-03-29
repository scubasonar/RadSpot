import gnu.io.*;
import java.io.*;
import java.net.*;
import java.text.*;
import java.util.*;
import java.util.zip.*;
import java.util.regex.*;

public class RadSpot implements SerialPortEventListener, Runnable {
	
	public class Worker implements Runnable {
		private Thread t;
		private Vector v;
		private boolean b = false;

		public Worker () {
			v = new Vector ();
			t = new Thread (this);
			t.start ();
		}
		
		public boolean getStatus () {
			return b;
		}

		public void add (Particle p) {
			v.addElement (p);
		}
		
		public void run () {
			b = true;
			System.out.println ("Worker started ...");
			while (b) {
				try {
					while (v.size() > 0) {
						Particle p = (Particle)v.elementAt(0);
						try {

							URL url = new URL ("http://radspot.org/collector.php?"+
								"i="+p.i+
								"&t="+p.l+
								"&lat="+p.latitude+
								"&lon="+p.longitude+
								"&alt="+p.altitude+
								"&cou="+p.course+
								"&spd="+p.speed+
								"&lbl=rs002"
							);

							/*System.out.println ("http://radspot.org/collector.php?"+
								"i="+p.i+
								"&t="+p.l+
								"&lat="+p.latitude+
								"&lon="+p.longitude+
								"&alt="+p.altitude+
								"&cou="+p.course+
								"&spd="+p.speed+
								"&lbl=rs001");*/
							url.getContent();
							URLConnection uc = url.openConnection();

							System.out.println ("[i="+p.i+
												", t="+p.l+
												", lat="+p.latitude+
												", lon="+p.longitude+
												", alt="+p.altitude+
												", cou="+p.course+
												", spd="+p.speed+"]"
							);

							v.removeElementAt (0);
						}
						catch (Exception e) {
							System.out.println ("Waiting for network ...");
							Thread.sleep (1000);
						}

					}
					t.sleep (10);

				}

				catch (Exception e) {
					System.out.println (e.toString());
					//System.out.println ("Waiting for network ...");
				}
			}
		}
	}

	public class Particle {
		public long i;
		public long l;
		public long latitude;
		public long longitude;
		public long altitude;
		public long course;
		public long speed;
		public Particle (long i, long l, long latitude, long longitude, long altitude, long course, long speed) {
			this.i = i;
			this.l = l;
			this.latitude = latitude;
			this.longitude = longitude;
			this.altitude = altitude;
			this.course = course;
			this.speed = speed;
		}
	}

	public static void main (String args[]) {
		
		try {
			System.out.println ("Building");
			RadSpot sp = new RadSpot ();
			Thread.sleep (1000);
			System.out.println ("Connecting");
			sp.connect();
			Thread.sleep (5000);
			int last = 0;
			System.out.println ("\n");
		}
		catch (Exception e) {
			System.out.println (e.toString());
		}
	}
	Worker w;
	Thread t;
	boolean ready = false;
	int speed = 0;
	SerialPort  thePort;
	public byte blinkm_addr = 0x09;
	//public String portName = "/dev/tty.usbserial-A9007LAN";
	public String portName = "COM5";
	final static int BAUDRATE = 115200;
	final static int FLOWCONTROL = SerialPort.FLOWCONTROL_NONE;
	final static int DATABITS = SerialPort.DATABITS_8;
	final static int STOPBITS = SerialPort.STOPBITS_1;
	final static int PARITY = SerialPort.PARITY_NONE;
	private InputStream input;
	private OutputStream output;
	private BufferedReader br;
	
	public RadSpot () {
		w = new Worker ();	
		t = new Thread (this);
		t.start ();
	}

	public void connect() throws Exception {
		RXTXCommDriver driver = new RXTXCommDriver ();
		Thread.sleep (1000);
		driver.initialize();
		Thread.sleep (1000);
		CommPortIdentifier portId = CommPortIdentifier.getPortIdentifier(portName);
		//thePort = (SerialPort)portId.open("/dev/tty.usbserial-A9007LAN", 200);
		thePort = (SerialPort)portId.open("COM5", 200);
		thePort.setSerialPortParams(BAUDRATE,DATABITS,STOPBITS,PARITY);
		thePort.addEventListener(this);
		thePort.notifyOnDataAvailable(true);
		input = thePort.getInputStream();
		output = thePort.getOutputStream();
		br = new BufferedReader (new InputStreamReader (input));
	}

	public synchronized void sendCommand( byte addr, byte[] cmd ) throws IOException {
		byte cmdfull[] = new byte[4+cmd.length];
		cmdfull[0] = 0x01;
		cmdfull[1] = addr;
		cmdfull[2] = (byte)cmd.length;
		cmdfull[3] = 0x00;
		for (int i=0; i<cmd.length; i++) {
			cmdfull[4+i] = cmd[i];
		}
		output.write(cmdfull);
		output.flush();
	}

	public synchronized void serialEvent(SerialPortEvent oEvent) {
		if (oEvent.getEventType() == SerialPortEvent.DATA_AVAILABLE) {
			try {
				if (!ready) {
                                	ready = true;
                        	}

			} catch (Exception e) {
				System.err.println(e.toString());
			}
		}
	}

	public void run () {
		try {
			while (!ready) {
				System.out.print ("*");
				t.sleep (100);
			}
			while (ready) {
				try {

					int i			= 0;
					int latitude	= 0;
					int longitude	= 0;
					int altitude	= 0;
					int course		= 0;
					int speed		= 0;
					
					// Intensity
					while (input.available() < 4)
						t.sleep(100);
					i = Integer.parseInt (br.readLine());
					
					// Latitude
					while (input.available() < 4)
						t.sleep(100);
					latitude	= Integer.parseInt (br.readLine());
					
					// Longitude
					while (input.available() < 4)
						t.sleep(100);
					longitude	= Integer.parseInt (br.readLine());

					// Altitude
					while (input.available() < 4)
						t.sleep(100);
					altitude	= Integer.parseInt (br.readLine());

					// Course
					while (input.available() < 4)
						t.sleep(100);
					course	= Integer.parseInt (br.readLine());

					// Speed
					while (input.available() < 4)
						t.sleep(100);
					speed	= Integer.parseInt (br.readLine());
					
					// Time
					long l = System.currentTimeMillis();
					w.add (new Particle (i, l, latitude, longitude, altitude, course, speed));
				}
				catch (Exception e) {
				}
				t.sleep (100);

			}
		}
		catch (Exception e) {
			System.out.println (e.toString());
		}
	}
}
